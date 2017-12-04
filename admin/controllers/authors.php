<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class AuthorListControllerAuthors extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}


	public function getModel($name = 'Author', $prefix = 'AuthorListModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}