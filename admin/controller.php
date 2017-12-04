<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListController extends JControllerLegacy
{
	protected $default_view = 'authors';

	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/authorlist.php';

		$view   = $this->input->get('view', 'authors');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'author' && $layout == 'edit' && !$this->checkEditId('com_authorlist.edit.author', $id)) {

			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_authorlist&view=authors', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
