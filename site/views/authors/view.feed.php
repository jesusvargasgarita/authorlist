<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListViewAuthors extends JViewLegacy
{
	function display()
	{
		$rows		= $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$app = JFactory::getApplication();

		$doc	= JFactory::getDocument();
		$params = $app->getParams();

		$doc->link = JRoute::_('index.php?option=com_authorlist&view=authors');

		foreach ($rows as $row)
		{
			$title = $this->escape($row->name);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

			$row->slug = $row->alias ? ($row->userid . ':' . $row->alias) : $row->userid;

			$link = JRoute::_(AuthorListHelperRoute::getAuthorRoute($row->slug), false);

			// strip html from feed item description text
			// TODO: Only pull fulltext if necessary (actually, just get the necessary fields).
			$description	= $row->description;

			$item = new JFeedItem();
			$item->title		= $title;
			$item->link			= $link;
			$item->description	= $description;

			$doc->addItem($item);
		}
	}
}
