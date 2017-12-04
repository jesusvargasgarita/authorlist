<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListModelAuthor extends JModelList
{
	
	protected $view_item = 'author';

	protected $_item = null;

	protected $_articles = null;

	protected $_context = 'com_authorlist.author';

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name', 'a.title',
				'alias', 'a.alias',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'hits', 'a.hits',
				'rating', 'a.publish_up',
				'author', 'a.author'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app	= JFactory::getApplication('site');
		
		$pk = $app->input->get('id', 0, 'int');
		$this->setState('author.id', $pk);
		$this->setState('user.id', 0);
		
		if ($pk) {
			$db	= $this->getDbo();
			$db->setQuery('SELECT userid FROM #__authorlist WHERE id =' . $pk);
			$this->setState('user.id', $db->loadResult());
		}

		$params = $app->getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		$this->setState('params', $mergedParams);
		
		$user = JFactory::getUser();
		
		if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
			
			if ($params->get('show_archived', 0)) {
				$this->setState('filter.published', array(1,2));
			} else {
				$this->setState('filter.published', 1);
			}
		}
		else
		{
			$this->setState('filter.published', array(0, 1, 2));
		}

		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}

		$this->setState('list.filter', $app->input->getString('filter-search'));

		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_authorlist.author.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_authorlist.author.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		
		if (($app->input->get('layout') == 'blog') || $params->get('layout_type') == 'blog') {
			$limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else {
			$limit = $app->getUserStateFromRequest('com_authorlist.author.list.' . $itemid . '.limit', 'limit', $params->get('display_num'));
		}
		
		$this->setState('list.limit', $limit);
		
		if($app->input->get('catid', 0, 'int')) { 
			$this->setState('filter.category_id', $app->input->get('catid'));
		}

	}

	function getItems()
	{
		$params = $this->getState()->get('params');
		$limit = $this->getState('list.limit');

		if ($this->_articles === null) {
			$model = JModelLegacy::getInstance('Articles', 'AuthorListModel', array('ignore_request' => true));
			$model->setState('params', JFactory::getApplication()->getParams());
			$model->setState('filter.published', $this->getState('filter.published',1));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('list.ordering', $this->_buildContentOrderBy());
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			$model->setState('filter.category_id', ($this->getState('filter.category_id')?array($this->getState('filter.category_id')):$params->get('catid')));
			$model->setState('filter.category_id.include', $params->get('category_mode'));
			$model->setState('filter.sub_category', $params->get('sub_category'));
			$userId = $this->getState('user.id');
			$groupId = $params->get('gid');
			if ( $userId != 0 ) {
				$model->setState('filter.author_id',$userId);
			} elseif ( $groupId != 0 ) {
				$model->setState('filter.group_id',$groupId);
			}
			if ($limit >= 0) {
				$this->_articles = $model->getItems();

				if ($this->_articles === false) {
					$this->setError($model->getError());
				}
			}
			else {
				$this->_articles=array();
			}

			$this->_pagination = $model->getPagination();
			
		}

		return $this->_articles;
	}

	protected function _buildContentOrderBy()
	{
		$app		= JFactory::getApplication('site');
		$db			= $this->getDbo();
		$params		= $this->state->params;
		$itemid		= $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol	= $app->getUserStateFromRequest('com_authorlist.author.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderDirn	= $app->getUserStateFromRequest('com_authorlist.author.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby	= ' ';

		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = null;
		}

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', ''))) {
			$orderDirn = 'ASC';
		}

		if ($orderCol && $orderDirn) {
			$orderby .= $orderCol . ' ' . $orderDirn . ', ';
		}

		$articleOrderby		= $params->get('orderby_sec', 'rdate');
		$articleOrderDate	= $params->get('order_date');
		$categoryOrderby	= $params->def('orderby_pri', '');
		$secondary			= AuthorListHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
		$primary			= AuthorListHelperQuery::orderbyPrimary($categoryOrderby);

		$orderby .= $primary . ' ' . $secondary . ' a.created ';

		return $orderby;
	}
	
	public function getAuthor() {
		$userId = $this->getState('user.id');
		if ($userId) {
			$author = new stdClass();
			$user = JUser::getInstance($userId);			
			$db	  = $this->getDbo();
			
			$query = $db->getQuery(true)
				->select('*')
				->from('#__authorlist')
				->where('userid=' . $userId);
			if(JLanguageMultilang::isEnabled()) :
				$query->where('(language='.$db->quote(JFactory::getLanguage()->getTag()).' OR language='.$db->quote('*').')');
			endif;
			$db->setQuery($query);
			$result = $db->loadObject();
			
			if ($result) {	
				$author = (object) array_merge((array) $author, (array) $result);
			
				$filter = JFilterInput::getInstance();	
				jimport('joomla.mail.helper');
				
				$author_slug = ($author->alias?$author->id.':'.$author->alias:$author->id);				
				
				$temp = clone ($this->getState('params'));
				$registry = new JRegistry();
				$registry->loadString($author->params);
				$temp->merge($registry);
				if ($author->display_alias) {
					$author->displayName = $author->display_alias;	
					$author_slug = $author->id.':'.JApplication::stringURLSafe($author->display_alias);
				} elseif ($temp->get('show_author_name') == 1) {
					$author->displayName = $user->username;	
				} else {
					$author->displayName = $user->name;
				}
				
				$this->setState('location', $filter->clean(JRoute::_(AuthorListHelperRoute::getAuthorRoute($author_slug))));
				
				if ($temp->get('show_email') == 1) {
					$author->email = trim($user->email);		
					if (!empty($author->email) && JMailHelper::isEmailAddress($author->email)) {
						$author->email = JHtml::_('email.cloak', $author->email);
					}
				}
				else {
					$author->email = '';
				}	
				if ($temp->get('gplus_url') && $this->getState('params')->get('enable_gplus', 1)) {
					$author->gplus_url = trim($temp->get('gplus_url'));		
					$author->gplus_icon = JURI::root() . 'media/com_authorlist/google-plus.png';
					if ($this->getState('params')->get('alt_gplus_icon')) {
						$author->gplus_icon = JURI::root() . $this->getState('params')->get('alt_gplus_icon');		
					}
				}
				else {
					$author->gplus_url = '';
				}	
				$author->thumb = $author->image;
				if ($temp->get('thumb_image') && $author->image) {
					$author->thumb = $this->getImage($author->image, $author->displayName, 'thumb');
				}
				if ($temp->get('resize_image') && $author->image) {
					$author->image = $this->getImage($author->image, $author->displayName);
				}
			} else {
				$author->link = $author->description = $author->image = $author->displayName = '';
			}
			
			$author->access_edit = 0;
			$curUser	= JFactory::getUser();

			if (!$curUser->get('guest') && isset($author->id)) {
				$curUserId	= $curUser->get('id');
				$asset	= 'com_authorlist.author.'.$author->id;

				if ($curUser->authorise('core.edit', $asset)) {
					$author->access_edit = 1;
				}
				elseif (!empty($curUserId) && $this->getState('params')->get('show_author_edit',1)) {
					if ($curUserId == $user->id) {
						$author->access_edit = 1;
					}
				}
			}
			
			return $author;
		}
		
		return null;
	}
	
	public function getImage($img, $author_name, $case='image') 
	{
		$userId = $this->getState('user.id');
		$params = $this->getState()->get('params');
		
		$width      = $params->get($case.'_width');
		$height     = $params->get($case.'_height');
		$proportion = $params->get('image_proportions','bestfit');
		$img_type   = $params->get('image_type','');
		
		$img_ext    = pathinfo($img, PATHINFO_EXTENSION);		
		
		$sub_folder = '0' . substr($userId, -1);
						
		if ( $img_type ) {
			$img_ext = $img_type;
		}
		
		$prefix = $userId . "_" . $case . "_" .substr($proportion,0,1) . "_".$width."_".$height;

		$thumb_file = $prefix . '.' . $img_ext;		
		
		$thumb_path = JPATH_BASE .'/media/com_authorlist/authors/' . $sub_folder . '/' . $thumb_file;
		
		$errors = array();
		
		if(file_exists($thumb_path))	{
			$size = @getimagesize($thumb_path);
			if($size) {
				$attribs['width']  = $size[0];
				$attribs['height'] = $size[1];
			}
		} else {
			
			$img_path   = JPATH_BASE  . '/' . $img;	
		
			$size = @getimagesize($img_path);
						
			if(!$size) 
			{
				$errors[] = 'There was a problem resizing the original image assigned to this author in Author List<br /><em>' . $img . '</em>. <br />Please make sure it exists!';
			
			} else {
								
				$origw = $size[0];
				$origh = $size[1];
				if( ($origw<$width && $origh<$height)) {
					$width = $origw;
					$height = $origh;
				}
				
				$this->calculateSize($origw, $origh, $width, $height, $proportion, $newwidth, $newheight, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	
				switch(strtolower($size['mime'])) {
					case 'image/png':
						$imagecreatefrom = "imagecreatefrompng";
						break;
					case 'image/gif':
						$imagecreatefrom = "imagecreatefromgif";
						break;
					case 'image/jpeg':
						$imagecreatefrom = "imagecreatefromjpeg";
						break;
				}
	
				
				if ( !function_exists ( $imagecreatefrom ) ) {
					$errors[] = "Failed to process author image in Author List. Function $imagecreatefrom doesn't exist.";
				}
				
				$src_img = $imagecreatefrom($img_path);
				
				if (!$src_img) {
					$errors[] = "There was a problem to process image (mime: ".$size['mime'] . ') in Author List';
				}
				
				$dst_img = ImageCreateTrueColor($width, $height);
				
				$bgcolor = hexdec($params->get('image_bg','#FFFFFF'));
				
				imagefill( $dst_img, 0,0, $bgcolor);
				if ( $proportion == 'transparent' ) {
					imagecolortransparent($dst_img, $bgcolor);
				}
				
				imagecopyresampled($dst_img,$src_img, $dst_x, $dst_y, $src_x, $src_y, $newwidth, $newheight, $src_w, $src_h);		
				
				switch(strtolower($img_ext)) {
					case 'png':
						$imagefunction = "imagepng";
						break;
					case 'gif':
						$imagefunction = "imagegif";
						break;
					default:
						$imagefunction = "imagejpeg";
				}
				
				if($imagefunction=='imagejpeg') {
					$result = @$imagefunction($dst_img, $thumb_path, 80 );
				} else {
					$result = @$imagefunction($dst_img, $thumb_path);
				}

				imagedestroy($src_img);
				
				if(!$result) {				
					if(!$disablepermissionwarning) {
					$errors[] = 'Could not create image:<br />' . $thumb_path . ' in Author List.<br /> Check if the folder exists and if you have write permissions:<br /> ' . JPATH_BASE .'/media/com_authorlist/authors/' . $sub_folder;
					}
					$disablepermissionwarning = true;
				} else {
					imagedestroy($dst_img);
				}
			}
		}
		
		if (count($errors)) {
			JError::raiseWarning(404, implode("\n", $errors));
			return false;
		}
				
		$image = "media/com_authorlist/authors/$sub_folder/" . basename($thumb_path);
		
		return  $image;
    }
	
	public function calculateSize($origw, $origh, &$width, &$height, &$proportion, &$newwidth, &$newheight, &$dst_x, &$dst_y, &$src_x, &$src_y, &$src_w, &$src_h) {
		
		if(!$width ) {
			$width = $origw;
		}

		if(!$height ) {
			$height = $origh;
		}

		if ( $height > $origh ) {
			$newheight = $origh;
			$height = $origh;
		} else {
			$newheight = $height;
		}
		
		if ( $width > $origw ) {
			$newwidth = $origw;
			$width = $origw;
		} else {
			$newwidth = $width;
		}
		
		$dst_x = $dst_y = $src_x = $src_y = 0;

		switch($proportion) {
			case 'fill':
			case 'transparent':
				$xscale=$origw/$width;
				$yscale=$origh/$height;

				if ($yscale<$xscale){
					$newheight =  round($origh/$origw*$width);
					$dst_y = round(($height - $newheight)/2);
				} else {
					$newwidth = round($origw/$origh*$height);
					$dst_x = round(($width - $newwidth)/2);

				}

				$src_w = $origw;
				$src_h = $origh;
				break;

			case 'crop':

				$ratio_orig = $origw/$origh;
				$ratio = $width/$height;
				if ( $ratio > $ratio_orig) {
					$newheight = round($width/$ratio_orig);
					$newwidth = $width;
				} else {
					$newwidth = round($height*$ratio_orig);
					$newheight = $height;
				}
					
				$src_x = ($newwidth-$width)/2;
				$src_y = ($newheight-$height)/2;
				$src_w = $origw;
				$src_h = $origh;				
				break;
				
 			case 'only_cut':
				// }
				$src_x = round(($origw-$newwidth)/2);
				$src_y = round(($origh-$newheight)/2);
				$src_w = $newwidth;
				$src_h = $newheight;
				
				break; 
				
			case 'bestfit':
				$xscale=$origw/$width;
				$yscale=$origh/$height;

				if ($yscale<$xscale){
					$newheight = $height = round($width / ($origw / $origh));
				}
				else {
					$newwidth = $width = round($height * ($origw / $origh));
				}
				$src_w = $origw;
				$src_h = $origh;	
				
				break;
			}

	}
	
	public function getAuthOptions()
	{
		if ($this->getState('params')->get('show_author_select',1)) {
			$filter = JFilterInput::getInstance();
			$this->setState('location', $filter->clean(JRoute::_(AuthorListHelperRoute::getAuthorRoute(0))));
	
			$authorsModel=JModelLegacy::getInstance('Authors', 'AuthorListModel', array('ignore_request' => true));
			$authorsModel->setState('list.ordering', 'a.ordering');
			$authorsModel->setState('list.direction', 'ASC');   
			$authorsModel->setState('filter.published', 1);
			$authorsModel->setState('filter.language', JLanguageMultilang::isEnabled());
			$groupId = $this->getState('params')->get('gid');
			if ( $groupId != 0 ) {
				$authorsModel->setState('filter.group_id',$groupId);
			}
			$options = $authorsModel->getItems();
			$count=count($options);
			if ($options > 0) {
				$filter = JFilterInput::getInstance();
				foreach($options as &$option)
				{	
					$temp = clone ($this->getState('params'));
					$registry = new JRegistry();
					$registry->loadString($option->params);
					$temp->merge($registry);
					$option->slug = ($option->alias?$option->id.':'.$option->alias:$option->id);
					if ($option->display_alias) {
						$option->text = $option->display_alias;	
						$option->slug = $option->id.':'.JApplication::stringURLSafe($option->display_alias);	
					} elseif ($temp->get('show_author_name') == 1) {
						$option->text = $option->username;	
					} else {
						$option->text = $option->name;
					}
					$option->value = $filter->clean(JRoute::_(AuthorListHelperRoute::getAuthorRoute($option->slug)));
				}
				$authors_order = $this->getState('params')->get('authors_order');
				
				if ($authors_order!='order') {
					if ($authors_order=='asc') {
						sort($options);	
					} else {
						rsort($options);	
					}
				}
				
				array_unshift($options, JHtml::_('select.option', $filter->clean(JRoute::_(AuthorListHelperRoute::getAuthorRoute(0))), JText::_('COM_AUTHORLIST_OPTION_SELECT_AUTHOR')));
				
				return $options;
			}
		}
		return null;
	}
	
	public function getCatOptions()
	{
		$params = $this->getState()->get('params');
		
		if ($params->get('show_category_filter',1)) {
		
			$user  = JFactory::getUser();
			
			$db    = $this->getDbo();
			
			$queryIn = 'SELECT a.catid FROM #__content a LEFT JOIN #__authorlist al ON al.userid = a.created_by';
			$queryInWhere = array();
			
			if($this->getState('user.id')) {
				$queryInWhere[] = 'a.created_by = '.$this->getState('user.id');	
			}	
			
			if($params->get('catid')) {
				$queryInWhere[] = 'a.catid IN ('.implode(',',$params->get('catid')).')';
			}
			
			if(JLanguageMultilang::isEnabled()) :
				$queryInWhere[] = '(a.language='.$db->quote(JFactory::getLanguage()->getTag()).' OR a.language='.$db->quote('*').')';
			endif;
			
			if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content'))) {		
				$nullDate	= $db->Quote($db->getNullDate());
				$nowDate	= $db->Quote(JFactory::getDate()->toSql());
				
				$queryInWhere[] = '(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')';
				$queryInWhere[] = '(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')';
			}
			
			if(count($queryInWhere)) {
				$queryIn .= ' WHERE '.implode(' AND ', $queryInWhere);
			}
			
			$queryIn .= ' GROUP BY a.catid';

			$query = $db->getQuery(true);
			
			$query->clear()
				->select('id, title')
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('id') . ' IN ('.$queryIn.')');
				
			if(JLanguageMultilang::isEnabled()) :
				$query->where('(language='.$db->quote(JFactory::getLanguage()->getTag()).' OR language='.$db->quote('*').')');
			endif;
				
			$db->setQuery($query);
			
			$options = $db->loadObjectList();
			
			if ($options > 0) {
				$filter = JFilterInput::getInstance();
				foreach($options as &$option)
				{	
					$option->text = $option->title;
					$option->value = $option->id;
				}
				
				array_unshift($options, JHtml::_('select.option', 0, JText::_('JGLOBAL_SELECT_AN_OPTION')));
				
				return $options;
			}
			return null;
		}	
		return null;
	}

	public function getPagination()
	{
		if (empty($this->_pagination)) {
			return null;
		}
		return $this->_pagination;
	}

}
