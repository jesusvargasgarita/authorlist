<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

 defined('_JEXEC') or die;

JLoader::register('AuthorListHelper', JPATH_ADMINISTRATOR . '/components/com_authorlist/helpers/authorlist.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

abstract class AuthorListHelperAssociation extends CategoryHelperAssociation
{
	public static function getAssociations($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = is_null($view) ? $jinput->get('view') : $view;
		$id = empty($id) ? $jinput->getInt('id') : $id;

		if ($view == 'author')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_authorlist', '#__authorlist', 'com_authorlist.author', $id, 'id', '', '');
				
				$return = array();

				foreach ($associations as $tag => $item)
				{
					$author_slug  = AuthorListHelperRoute::getAuthorSlug($item->id);		
				
					$return[$tag] = AuthorListHelperRoute::getAuthorRoute($author_slug, $item->language);
				}

				return $return;
			}
		}

		return array();

	}
}
