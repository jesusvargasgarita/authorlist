<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListTableAuthor extends JTable
{
	public function __construct(& $db)
	{
		parent::__construct('#__authorlist', 'id', $db);
	}

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
		
		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	public function store($updateNulls = false)
	{
		if (is_array($this->params)) {
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = (string)$registry;
		}
		
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();		
			
		$db	= JFactory::getDBO();
		$db->setQuery('SELECT username FROM #__users WHERE id='.$this->userid);
		
		$this->alias = $db->loadResult();

		$this->alias = JApplication::stringURLSafe($this->alias);
		
		$table = JTable::getInstance('Author', 'AuthorListTable');
		if (!$this->userid) {
			$this->setError(JText::_('COM_AUTHORLIST_STORE_ERROR_USER_REQUIRED'));
			return false;
		}
		if ($table->load(array('userid' => $this->userid)) && ($table->id != $this->id || $this->id == 0))
		{
			$app = JFactory::getApplication();
			$assoc = JLanguageAssociations::isEnabled();
			if($assoc) {
				if($table->language == $this->language || $this->language == '*' || $table->language == '*') {
					$this->setError(JText::_('COM_AUTHORLIST_STORE_ERROR_AUTHOR_EXISTS_LANGUAGE'));
					return false;
				}
			} else {
				$this->setError(JText::_('COM_AUTHORLIST_STORE_ERROR_AUTHOR_EXISTS'));
				return false;
			}
		}
		
		if (!$this->id) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
			
			// Include user into the Editor Group
			$db->setQuery('SELECT user_id FROM #__user_usergroup_map WHERE user_id = '.$this->userid.' AND group_id = 3');
			if (!$db->loadResult()) {			
				$db->setQuery('INSERT INTO `#__user_usergroup_map` (`user_id`,`group_id`) VALUES ('.$this->userid.',3)');
				$db->query();
			}
			
		}

		return parent::store($updateNulls);
	}

	function check()
	{
		
		$registry = new JRegistry();
		$registry->loadString($this->params);
		$params = $registry->toArray();
		
		if ((strlen($params['gplus_url']) > 0)
			&& (stripos($params['gplus_url'], 'http://') === false)
			&& (stripos($params['gplus_url'], 'https://') === false))
		{
			$this->setError(JText::_('COM_AUTHORLIST_WARNING_GPLUS_URL'));
			return false;
		}
		
		if (trim($this->userid) == '') {
			$this->setError(JText::_('COM_AUTHORLIST_WARNING_USER'));
			return false;
		}
			
		return true;
		// clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();
			foreach($keys as $key) {
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		// clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc)) {
			// only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = JString::str_ireplace($bad_characters, "", $this->metadesc);
		}
		return true;
	}
	
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			}
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		$where = $k.'='.implode(' OR '.$k.'=', $pks);

		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `state` = '.(int) $state .
			' WHERE ('.$where.')' .
			$checkin
		);
		$this->_db->query();

		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
			foreach($pks as $pk) {
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}
}
