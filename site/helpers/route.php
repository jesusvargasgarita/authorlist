<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

abstract class AuthorListHelperRoute
{
	protected static $lookup;

	protected static $lang_lookup = array();

	public static function getAuthorRoute($id, $lang = 0)
	{
		$app = JFactory::getApplication();
		
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT m.group_id FROM #__user_usergroup_map m
			 LEFT JOIN #__users u ON u.id = m.user_id 
			 LEFT JOIN #__authorlist a ON a.userid = u.id
			 WHERE a.id = ' . (int)$id
		);
		$group_ids = $db->loadObjectList();
		$gids = array();
		foreach($group_ids as $gid) {
			$gids[] = (int)$gid->group_id;
		}
		//var_dump($group_ids);
		$needles = array(
			'author'  => array((int) $id),
			'gids' => $gids,
			'authors'  => array()
		);
		
		$layout = '';
		if ($app->input->get('layout') == 'blog') {
			$layout = '&layout=blog';
		}
		
		$link = 'index.php?option=com_authorlist&view=author'.$layout.'&id='. $id;
		
		if (!$lang && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
			
			$lang = JFactory::getLanguage()->getTag();
		}
		
		if (isset(self::$lang_lookup[$lang]))
		{
			$link .= '&lang=' . self::$lang_lookup[$lang];
			$needles['language'] = $lang;
		}
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item; 
		}
		elseif ($item = self::_findItem(array('author'=>array(0)))) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}
	
	protected static function buildLanguageLookup()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component  = JComponentHelper::getComponent('com_authorlist');
			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);
			if ($items) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$view = $item->query['view'];
						if ($view === 'authors') {
							self::$lookup[$view] = $item->id;
						}
						if (!isset(self::$lookup[$language][$view]))
						{
							if ($view === 'authors') {
								self::$lookup[$language][$view] = array($item->id);
							} else {
								self::$lookup[$language][$view] = array();
							}
						}
						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}
						if (isset($item->query['id']))
						{
							/**
							* Here it will become a bit tricky
							* language != * can override existing entries
							* language == * cannot override existing entries
							*/
							if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
							{
								self::$lookup[$language][$view][$item->query['id']] = $item->id;
							}
						}
					}
					$gid = $item->params->get('gid');
					if($gid) {
						self::$lookup['gids'][$gid] = $item->id;
					}
				}
			}
		}
		
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
					if(isset(self::$lookup[$language][$view][0])) 
					{
						return self::$lookup[$language][$view][0];	
					}
				}
			}
		}
	}
	
	public static function getAuthorSlug($id)
	{
		$db	  = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__authorlist')
			->where('id=' . $id);
		$db->setQuery($query);
		$author = $db->loadObject();

		$author_slug = ($author->alias?$author->id.':'.$author->alias:$author->id);	
		
		if ($author->display_alias) {
			$author_slug = $author->id.':'.JApplication::stringURLSafe($author->display_alias);	
		}
		return $author_slug;			
	}
}
