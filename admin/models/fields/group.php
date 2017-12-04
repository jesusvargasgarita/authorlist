<?php
/*------------------------------------------------------------------------
# com_authorlist - Author List
# ------------------------------------------------------------------------
# author    Jesús Vargas Garita
# copyright Copyright (C) 2013 www.munditico.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.munditico.com
# Technical Support:  Forum - http://www.munditico.com/forum
-------------------------------------------------------------------------*/

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldGroup extends JFormFieldList
{
	protected $type = 'Group';

	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text')
			->from('#__usergroups AS a')
			->where('a.parent_id=3');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		// Merge any additional options in the XML definition.
		$options[] = JHtml::_('select.option', '0', JText::_('COM_AUTHORLIST_OPTION_NO_GROUP_LABEL'));
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
