<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_authorlist'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller	= JControllerLegacy::getInstance('AuthorList');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
