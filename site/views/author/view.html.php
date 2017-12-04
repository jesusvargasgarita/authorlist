<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListViewAuthor extends JViewLegacy
{
	protected $state;
	protected $params;
	protected $items;
	protected $pagination;

	protected $lead_items = array();
	protected $intro_items = array();
	protected $link_items = array();
	protected $columns = 1;

	function display($tpl = null)
	{
		$db		= JFactory::getDBO();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		
		$filter = JFilterInput::getInstance();

		$state		 = $this->get('State');
		$params		 = $state->params;
		$items		 = $this->get('Items');
		$pagination  = $this->get('Pagination');
		$authOptions = $this->get('AuthOptions');
		$catOptions  = $this->get('CatOptions');
		
		$author_id = $state->get('author.id');
		
		if ($author_id != 0) {	
			$author	= $this->get('Author');
		}

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
				
		$numLeading	= $params->def('num_leading_articles', 1);
		$numIntro	= $params->def('num_intro_articles', 4);
		$numLinks	= $params->def('num_links', 4);

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = &$items[$i];
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

			$item->event = new stdClass();

			$dispatcher = JDispatcher::getInstance();

			if ($i < $numLeading + $numIntro) {
				$item->introtext = JHtml::_('content.prepare', $item->introtext);
				
				if ($params->get('content_plugins', 0)) {

					$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.article', &$item, &$item->params, 0));
					$item->event->afterDisplayTitle = trim(implode("\n", $results));

					$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.article', &$item, &$item->params, 0));
					$item->event->beforeDisplayContent = trim(implode("\n", $results));

					$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.article', &$item, &$item->params, 0));
					$item->event->afterDisplayContent = trim(implode("\n", $results));
				}
			}
		}
		
		if (!$params->get('layout_type')) {
			$active	= $app->getMenu()->getActive();		
			$layout = $params->get('author_layout');		
			if (isset($active->query['layout'])) {
				$this->setLayout($active->query['layout']);
			} else {
				$this->setLayout($layout);
			}
		}
		
		if (($params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog')) {
			
			$this->setLayout('blog');
			
			$max = count($items);

			$limit = $numLeading;
			for ($i = 0; $i < $limit && $i < $max; $i++) {
				$this->lead_items[$i] = &$items[$i];
			}

			$limit = $numLeading + $numIntro;
			for ($i = $numLeading; $i < $limit && $i < $max; $i++) {
				$this->intro_items[$i] = &$items[$i];
			}

			$this->columns = max(1, $params->get('num_columns', 2));
			$order = $params->def('multi_column_order', 1);

			if ($order == 0 && $this->columns > 1) {
				$this->intro_items = AuthorListHelperQuery::orderDownColumns($this->intro_items, $this->columns);
			}

			$limit = $numLeading + $numIntro + $numLinks;
			for ($i = $numLeading + $numIntro; $i < $limit && $i < $max;$i++)
			{
				$this->link_items[$i] = &$items[$i];
			}
		}
		
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assignRef('state', $state);
		$this->assignRef('items', $items);
		$this->assignRef('author', $author);
		$this->assignRef('authOptions', $authOptions);
		$this->assignRef('catOptions', $catOptions);
		$this->assignRef('params', $params);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('user', $user);
		
		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		
		$author_id = $this->state->get('author.id');

		$menu = $menus->getActive();
		
		$title = $this->params->get('page_title');

		if ($menu) {
			$currentLink = $menu->link;
			if (strpos($currentLink, 'view=author') && (strpos($currentLink, '&id='.(string) $author_id))) {
				$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			}
			elseif ($author_id) {
				$this->params->def('page_heading', JText::_('COM_AUTHORLIST_AUTHOR_PAGE_HEADING'));
				$title = sprintf(JText::_('COM_AUTHORLIST_AUTHOR_PAGE_TITLE'), $this->author->displayName);
				$pathway->addItem($this->author->displayName);
			}
		}
		elseif ($author_id) {
			$this->params->def('page_heading', JText::_('COM_AUTHORLIST_AUTHOR_PAGE_HEADING'));
			$title = sprintf(JText::_('COM_AUTHORLIST_AUTHOR_PAGE_TITLE'), $this->author->displayName);
			$pathway->addItem($this->author->displayName);
		}
		else {
			$this->params->def('page_heading', JText::_('COM_AUTHORLIST_AUTHORS_PAGE_HEADING'));
			$title = JText::_('COM_AUTHORLIST_AUTHORS_PAGE_HEADING');
		}
				
		$this->document->setTitle($title);

		if ($author_id && $this->author->metadesc)
		{
			$this->document->setDescription($this->author->metadesc);
		}
		elseif ($this->params->get('menu-meta_description')) 
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($author_id && $this->author->metakey)
		{
			$this->document->setMetadata('keywords', $this->author->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords')) 
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($author_id && $this->params->get('robots')) 
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($author_id && $app->getCfg('MetaTitle') == '1' && $this->author->displayName) {
			$this->document->setMetaData('title', $this->author->displayName);
		}
		if ( $author_id ) {
			
			$registry = new JRegistry;
			$registry->loadString($this->author->metadata);
			$metadata = $registry;
			
			$mdata = $metadata->toArray();
			
			foreach ($mdata as $k => $v)
			{
				if ($v) {
					$this->document->setMetadata($k, $v);
				}
			}
		}
	}
}
