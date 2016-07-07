<?php
/**
 * @package		Spir
 * @date	    16/7/4
 * @author		Irvin
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once ( APPPATH.'/third_party/smarty/libs/Smarty.class.php' );

class Smartie extends Smarty {

    var $debug = false;

    function __construct()
    {
        parent::__construct();

        $this->template_dir = APPPATH . "views/templates";
        $this->compile_dir = APPPATH . "views/templates_c";
        $this->config_dir = APPPATH . "source_map/config";
        $this->left_delimiter = "{%";
        $this->right_delimiter = "%}";
        if ( ! is_writable( $this->compile_dir ) )
        {
            // make sure the compile directory can be written to
            @chmod( $this->compile_dir, 0777 );
        }
        $smarty_plugin_dir = APPPATH . "third_party/smarty/fis-plus-smarty-plugin";
        $this->plugins_dir = $smarty_plugin_dir;
        $this->assign( 'FCPATH', FCPATH );     // path to website
        $this->assign( 'APPPATH', APPPATH );   // path to application directory
        $this->assign( 'BASEPATH', BASEPATH ); // path to system directory

        if ( method_exists( $this, 'assignByRef') )
        {
            $sp = &get_instance();
            $this->assignByRef("sp", $sp);
        }

        log_message('debug', "Smarty Class Initialized");
    }

    function setDebug( $debug=true )
    {
        $this->debug = $debug;
    }

    /**
     *
     * @access    public
     * @param    string
     * @param    array
     * @param    bool
     * @return    string
     */
    function view($template, $data = array(), $return = FALSE)
    {
        if ( ! $this->debug )
        {
            $this->error_reporting = false;
        }
        $this->error_unassigned = false;

        foreach ($data as $key => $val)
        {
            $this->assign($key, $val);
        }

        if ($return == FALSE)
        {
            $SP =& get_instance();
            if (method_exists( $SP->output, 'set_output' ))
            {
                $SP->output->set_output( $this->fetch($template) );
            }
            else
            {
                $SP->output->final_output = $this->fetch($template);
            }
            return;
        }
        else
        {
            return $this->fetch($template);
        }
    }
}