<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class SP_Service
{
    /**
     * Class constructor
     * @return	void
     */
    public function __construct()
    {
        log_message('debug', "Service Class Initialized");
    }

    /**
     * __get magic
     *
     * Allows models to access SP's loaded classes using the same
     * syntax as controllers.
     *
     * @param	string	$key
     */
    public function __get($key)
    {
        return get_instance()->$key;
    }
}


