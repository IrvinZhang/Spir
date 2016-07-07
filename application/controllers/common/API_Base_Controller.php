<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 接口编写基础控制器
 */
class API_Base_Controller extends SP_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function output2JSON($response, $stateCode = 200)
    {
        $this->output
            ->set_status_header($stateCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();

        exit;
    }
}