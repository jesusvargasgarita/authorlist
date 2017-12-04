<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListHelper extends JHelperContent
{
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_AUTHORLIST_SUBMENU_AUTHORS'),
			'index.php?option=com_authorlist&view=authors',
			$vName == 'authors'
		);
	}
}
