<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class AuthorListViewAuthor extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);

		JToolBarHelper::title(JText::_('COM_AUTHORLIST_PAGE_'.($isNew ? 'ADD_AUTHOR' : 'EDIT_AUTHOR')), 'user-add.png');

		// Built the actions for new and existing records.
		if ($isNew)  {
			// For new records, check the create permission.
			JToolBarHelper::apply('author.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('author.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('author.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::cancel('author.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::apply('author.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('author.save', 'JTOOLBAR_SAVE');
			// We can save this record, but check the create permission to see if we can return to make a new one.
			JToolBarHelper::custom('author.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			JToolBarHelper::custom('author.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			JToolBarHelper::cancel('author.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
