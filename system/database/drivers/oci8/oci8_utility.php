<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Oracle Utility Class
 */
class SP_DB_oci8_utility extends SP_DB_utility {

	/**
	 * List databases statement
	 *
	 * @var	string
	 */
	protected $_list_databases	= 'SELECT username FROM dba_users'; // Schemas are actual usernames

	/**
	 * Export
	 *
	 * @param	array	$params	Preferences
	 * @return	mixed
	 */
	protected function _backup($params = array())
	{
		// Currently unsupported
		return $this->db->display_error('db_unsupported_feature');
	}

}
