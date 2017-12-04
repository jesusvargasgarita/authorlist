<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListModelArticles extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$orderCol = $app->input->get('filter_order', 'a.ordering');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		$user		= JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_authorlist')) &&  (!$user->authorise('core.edit', 'com_authorlist'))){
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}

		$this->setState('layout', $app->input->get('layout'));
		
		$this->setState('query.where','');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':'.serialize($this->getState('filter.published'));
		$id .= ':'.$this->getState('filter.access');
		$id .= ':'.serialize($this->getState('filter.author_id'));
		$id .= ':'.$this->getState('filter.date_filtering');
		$id .= ':'.serialize($this->getState('filter.category_id'));
		$id .= ':'.$this->getState('filter.category_id.include');
		return parent::getStoreId($id);
	}

	function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		
		$user  = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.introtext, a.images, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// use created if modified is 0
				'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, ' .
					'a.modified_by, uam.name as modified_by_name,' .
				// use created if publish_up is 0
				'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up, ' .
					'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, '.
					'a.hits, a.xreference, a.featured,'.' LENGTH(a.fulltext) AS readmore '
			)
		);

		if ($this->getState('filter.published') == 2) {
			$query->select($this->getState('list.select','CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
		}
		else {
			$query->select($this->getState('list.select','CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
		}

		$query->from('#__content AS a');

		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		$query->select('( v.rating_sum / v.rating_count ) AS rating, v.rating_count as rating_count');
		$query->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

		// Join to check for category published state in parent categories up the tree
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');

		if ($this->getState('filter.published') == 2) {
			$subquery .= ' AND parent.published = 2 GROUP BY cat.id ';
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
		}
		else {
			$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
		}
		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

		if ($this->getState('query.where')) {
			$query->where($this->getState('query.where'));
		}
	
		if ($access = $this->getState('filter.access')) {
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}
		
		$categoryId = $this->getState('filter.category_id');
		
		if (is_array($categoryId) && (count($categoryId) > 0)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			if (!empty($categoryId)) {
				$type = $this->getState('filter.category_id.include', 1) ? 'IN' : 'NOT IN';
				$query->where('a.catid '.$type.' ('.$categoryId.')');
			}
		}

		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where($publishedWhere . ' = ' . (int) $published);
		}
		else if (is_array($published)) {
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			$query->where($publishedWhere . ' IN ('.$published.')');
		}
		
		if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content')))
		{		
			$nullDate	= $db->Quote($db->getNullDate());
			$nowDate	= $db->Quote(JFactory::getDate()->toSql());
		
			$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
			$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
		}

		$authorId = $this->getState('filter.author_id');
		$groupId = $this->getState('filter.group_id');
		if ($authorId) {
			$query->where('a.created_by ='.(int) $authorId);
		} elseif ($groupId) {
			$query->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = ua.id')
				->where('map.group_id = ' . (int) $groupId);
		}

		$params = $this->getState('params');

		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $this->getState('list.filter'))) {
			$filter = JString::strtolower($filter);
			$hitsFilter = intval($filter);
			$filter = $db->Quote('%'.$db->escape($filter, true).'%', false);

			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where(
						'LOWER( CASE WHEN a.created_by_alias > '.$db->quote(' ').
						' THEN a.created_by_alias ELSE ua.name END ) LIKE '.$filter.' '
					);
					break;

				case 'hits':
					$query->where('a.hits >= '.$hitsFilter.' ');
					break;

				case 'title':
				default: // default to 'title' if parameter is not valid
					$query->where('LOWER( a.title ) LIKE '.$filter);
					break;
			}
		}
		
		if ($this->getState('filter.language', JLanguageMultilang::isEnabled())) {
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$query->order($this->getState('list.ordering', 'a.ordering').' '.$this->getState('list.direction', 'ASC'));
		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

	public function getItems()
	{
		$items	= parent::getItems();
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$guest	= $user->get('guest');
		$groups	= $user->getAuthorisedViewLevels();
		$input  = JFactory::getApplication()->input;

		$globalParams = JComponentHelper::getParams('com_content', true);

		foreach ($items as &$item)
		{
			$articleParams = new JRegistry;
			$articleParams->loadString($item->attribs);

			$item->alternative_readmore = $articleParams->get('alternative_readmore');
			$item->layout = $articleParams->get('layout');

			$item->params = clone $this->getState('params');

			// For blogs, article params override menu item params only if menu param = 'use_article'
			// Otherwise, menu item params control the layout
			// If menu item is 'use_article' and there is no article param, use global
			if (($input->getString('layout') == 'blog') || ($this->getState('params')->get('layout_type') == 'blog')) {
				// create an array of just the params set to 'use_article'
				$menuParamsArray = $this->getState('params')->toArray();
				$articleArray = array();

				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_article') {
						// if the article has a value, use it
						if ($articleParams->get($key) != '') {
							// get the value from the article
							$articleArray[$key] = $articleParams->get($key);
						}
						else {
							// otherwise, use the global value
							$articleArray[$key] = $globalParams->get($key);
						}
					}
				}

				// merge the selected article params
				if (count($articleArray) > 0) {
					$articleParams = new JRegistry;
					$articleParams->loadArray($articleArray);
					$item->params->merge($articleParams);
				}
			}
			else {
				// For non-blog layouts, merge all of the article params
				$item->params->merge($articleParams);
			}

			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			// Compute the asset access permissions.
			// Technically guest could edit an article, but lets not check that to improve performance a little.
			if (!$guest) {
				$asset	= 'com_authorlist.article.'.$item->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset)) {
					$item->params->set('access-edit', true);
				}
				// Now check if edit.own is available.
				else if (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by) {
						$item->params->set('access-edit', true);
					}
				}
			}

			$access = $this->getState('filter.access');

			if ($access) {
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			}
			else {
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null) {
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else {
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
		}

		return $items;
	}
	public function getStart()
	{
		return $this->getState('list.start');
	}
}
