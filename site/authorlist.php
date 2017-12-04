<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::register('AuthorListHelperRoute', JPATH_COMPONENT . '/helpers/route.php');
JLoader::register('AuthorListHelperQuery', JPATH_COMPONENT . '/helpers/query.php');

$doc = JFactory::getDocument();
$doc->addStyleSheet('components/com_authorlist/authorlist.css');

$controller = JControllerLegacy::getInstance('AuthorList');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
