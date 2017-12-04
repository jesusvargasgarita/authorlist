<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.mail.helper');

class AuthorListViewAuthors extends JViewLegacy
{
	protected $state;
	protected $items;
	protected $authors;
	protected $pagination;

	function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$user	= JFactory::getUser();

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item		= &$items[$i];
			if ($item->display_alias) {
				$item->slug = $item->id.':'.JApplication::stringURLSafe($item->display_alias);		
			} elseif ($item->alias) {
				$item->slug = $item->id.':'.$item->alias;	
			} else {
				$item->slug = $item->id;
			}
			$temp		= new JRegistry();
			$temp->loadString($item->params);
			$item->params = clone($params);
			$item->params->merge($temp);
			
			if ($item->display_alias) {
				$item->displayName = $item->display_alias;	
			} elseif ($params->get('show_author_name') == 1 || $item->params->get('show_author_name') == 1 ) {
				$item->displayName = $item->username;	
			} else {
				$item->displayName = $item->name;
			}

			if ($item->params->get('show_email', 0) == 1) {
				$item->email = trim($item->email);

				if (!empty($item->email) && JMailHelper::isEmailAddress($item->email)) {
					$item->email = JHtml::_('email.cloak', $item->email);
				}
			}
			else {
				$item->email = '';
			}
			
			$item->access_edit = 0;

			if (!$user->get('guest')) {
				$userId	= $user->get('id');
				$asset	= 'com_authorlist.author.'.$item->id;

				if ($user->authorise('core.edit', $asset)) {
					$item->access_edit = 1;
				}
				elseif (!empty($userId) && $params->get('show_author_edit',1)) {
					if ($userId == $item->userid) {
						$item->access_edit = 1;
					}
				}
			}
		}

		$maxLevel = $params->get('maxLevel', -1);
		$this->maxLevel   = &$maxLevel;
		$this->state      = &$state;
		$this->items      = &$items;
		$this->params     = &$params;
		$this->pagination = &$pagination;

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title 		= null;

		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_AUTHORLIST_AUTHORS_PAGE_HEADING'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) 
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) 
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) 
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
