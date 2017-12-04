<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListControllerAuthor extends JControllerForm
{
	protected $view_item = 'form';

	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId	= (int) isset($data[$key]) ? $data[$key] : 0;
		$user		= JFactory::getUser();
		$asset		= 'com_authorlist.author.'.$recordId;
		
		$db = JFactory::getDbo();
		$query = 'SELECT userid FROM #__authorlist WHERE id=' . $recordId;
		$db->setQuery($query);
		$user_id = $db->loadResult();
		
		$params = JComponentHelper::getParams('com_authorlist');

		if ($user->authorise('core.edit', $asset)) {
			return true;
		}

		if ($params->get('show_author_edit',1)) { 
			if ($user_id == $user->get('id')) {
				return true;
			}
		}
		return parent::allowEdit($data, $key);
	}

	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		$this->setRedirect($this->getReturnPage());
	}

	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}

	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		// Need to override the parent method completely.
		$layout		= $this->input->get('layout', 'edit');
		$append		= '';

		// TODO This is a bandaid, not a long term solution.
//		if ($layout) {
//			$append .= '&layout='.$layout;
//		}
		$append .= '&layout=edit';

		if ($recordId) {
			$append .= '&'.$urlVar.'='.$recordId;
		}

		$itemId	= $this->input->getInt('Itemid');
		$return	= $this->getReturnPage();

		if ($itemId) {
			$append .= '&Itemid='.$itemId;
		}

		if ($return) {
			$append .= '&return='.base64_encode($return);
		}

		return $append;
	}

	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JURI::base();
		}
		else {
			return base64_decode($return);
		}
	}

	public function save($key = null, $urlVar = 'a_id')
	{

		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result) {
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

}
