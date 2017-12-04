<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class AuthorListModelAuthors extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'u.name',
				'username', 'u.username',
				'email', 'a.email',
				'ordering', 'a.ordering',
				'articles_count',
			);
		}

		parent::__construct($config);
	}

	public function &getItems()
	{
		$items = parent::getItems();

		for ($i = 0, $n = count($items); $i < $n; $i++) {
			$item = &$items[$i];
			if (!isset($this->_params)) {
				$params = new JRegistry();
				$params->loadString($item->params);
				$item->params = $params;
			}
		}

		return $items;
	}

	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params	= JComponentHelper::getParams('com_authorlist');
		
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select($this->getState('list.select', 'u.name AS name,u.username AS username,u.email AS email,a.*'));
		
		$query->from('`#__users` AS u');
				
		$query->join('INNER', '#__authorlist AS a ON a.userid = u.id');
		
		$query->where('a.access IN ('.$groups.')');

		$state = $this->getState('filter.published', 1);
		if (is_numeric($state)) {
			$query->where('a.state = '.(int) $state);
		}
		
		if ($this->getState('filter.language'))
		{
			$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}
		
		$groupId = $this->getState('filter.group_id', 0);
		if ( $groupId != 0 ) {
			$query->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = a.userid')
				->where('map.group_id = ' . (int) $groupId);
		}
		
		if ($params->get('show_archived', 0)) {
			$content_where_and = ' AND c.state IN (1,2)';;	
		} else {
			$content_where_and = ' AND c.state = 1';	
		}	
		
		$query->select('(SELECT COUNT(*) FROM #__content c WHERE c.created_by = u.id'.$content_where_and.') AS articles_count');

		$query->order($db->escape($this->getState('list.ordering', 'a.ordering')).' '.$db->escape($this->getState('list.direction', 'ASC')));
		
		return $query;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app	= JFactory::getApplication();
		$params	= JComponentHelper::getParams('com_authorlist');
		$db		= $this->getDbo();
		
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		
		$format = $app->input->getWord('format');
		if ($format=='feed') {
			$limit = $app->getCfg('feed_limit');
		}
		else {
			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		}
		$this->setState('list.limit', $limit);

		$limitstart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);
		
		$groupId = $mergedParams->get('gid');
		if ( $groupId != 0 ) {
			$this->setState('filter.group_id',$groupId);
		}
		
		$order = 'a.ordering';
		$direc = 'ASC';
		if ($mergedParams->get('authors_order') != 'order') {
			if ($mergedParams->get('show_author_name') == 1) {
				$order = 'u.username';
			} else {
				$order = 'u.name';

			}
			$direc = $mergedParams->get('authors_order');
		}


		$orderCol	= $app->input->get('filter_order', $order);
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder	=  $app->input->get('filter_order_Dir', $direc);
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', strtoupper($listOrder));

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_authorlist')) &&  (!$user->authorise('core.edit', 'com_authorlist'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.publish_date', true);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		$this->setState('params', $mergedParams);
	}

}
