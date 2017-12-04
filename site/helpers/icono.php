<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class JHtmlIcono
{
	static function edit($author, $params, $attribs = array())
	{
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$uri	= JFactory::getURI();

		JHtml::_('behavior.tooltip');

		$url	= 'index.php?option=com_authorlist&task=author.edit&a_id='.$author->id.'&return='.base64_encode($uri);		
		$text = '<i class="hasTip icon-edit tip" title="'.JText::_('COM_AUTHORLIST_AUTHOR_EDIT').'"></i> '.JText::_('JGLOBAL_EDIT');

		$output = JHtml::_('link', JRoute::_($url), $text);

		return $output;
	}
}
