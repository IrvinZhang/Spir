<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CUBRID Utility Class
 */
class SP_DB_cubrid_utility extends SP_DB_utility {

	/**
	 * List databases
	 *
	 * @return	array
	 */
	public function list_databases()
	{
		if (isset($this->db->data_cache['db_names']))
		{
			return $this->db->data_cache['db_names'];
		}

		return $this->db->data_cache['db_names'] = cubrid_list_dbs($this->db->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * CUBRID Export
	 *
	 * @param	array	Preferences
	 * @return	mixed
	 */
	protected function _backup($params = array())
	{
		// No SQL based support in CUBRID as of version 8.4.0. Database or
		// table backup can be performed using CUBRID Manager
		// database administration tool.
		return $this->db->display_error('db_unsupported_feature');
	}
}
