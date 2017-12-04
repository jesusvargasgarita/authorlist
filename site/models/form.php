<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_authorlist/models/author.php';

class AuthorListModelForm extends AuthorListModelAuthor
{
	protected function populateState()
	{
		$app = JFactory::getApplication();

		$pk = $app->input->getInt('a_id');
		$this->setState('author.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	public function getAuthor()
	{
		$user = JFactory::getUser();
		$userId	= $user->get('id');

		$db = $this->getDbo();
		$author_id = $this->getState('author.id');
		
		if(!$author_id && !$user->get('guest')) :
			$query = 'SELECT id FROM #__authorlist WHERE userid=' . $userId;
			$db->setQuery($query);
			$author_id = $db->loadResult();
		endif;
		
		if($author_id) :
		
			$query = 'SELECT * FROM #__authorlist WHERE id=' . $author_id;
			$db->setQuery($query);
			$author = $db->loadObject();
			
			if ($author) :
				
				$author->access_edit = 0;
				if (!$user->get('guest')) {
					$asset	= 'com_authorlist.author.'.$author->id;
		
					if ($user->authorise('core.edit', $asset)) {
						$author->access_edit = 1;
					}
					elseif (!empty($userId) && $this->getState('params')->get('show_author_edit',1)) {
						if ($userId == $author->userid) {
							$author->access_edit = 1;
						}
					}
				}
				
				$query = 'SELECT name, email FROM #__users WHERE id=' . $author->userid;
				$db->setQuery($query);
				$row = $db->loadObject();
				
				$author->name  = $row->name;
				
				return $author;
			
			endif;
			
		endif;
		
		return false;
		
	}

	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}
	
	public function save($data)
	{
		$data['params'] = array(
			'show_email' => $data['show_email'],
			'show_author_name' => $data['show_author_name'],	
			'gplus_url' => $data['gplus_url']				
		);
		unset($data['show_email']);
		unset($data['show_author_name']);
		unset($data['gplus_url']);
		
		if (parent::save($data)) {
			return true;
		}

		return false;
	}
}
