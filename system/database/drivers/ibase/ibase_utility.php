<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Interbase/Firebird Utility Class
 *
 */
class SP_DB_ibase_utility extends SP_DB_utility {

	/**
	 * Export
	 *
	 * @param	string	$filename
	 * @return	mixed
	 */
	protected function _backup($filename)
	{
		if ($service = ibase_service_attach($this->db->hostname, $this->db->username, $this->db->password))
		{
			$res = ibase_backup($service, $this->db->database, $filename.'.fbk');

			// Close the service connection
			ibase_service_detach($service);
			return $res;
		}

		return FALSE;
	}

}
