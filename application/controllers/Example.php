<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 事例接口控制器
 */
require_once ('common/API_Base_Controller.php');
class Example extends API_Base_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->load->service("API_Example_service");
    }
    public function getUserInfo()
    {
        $uid = $this->input->get('uid');
        $resData = $this->api_example_service->getUserInfoByUid($uid);

        parent::output2JSON($resData);
    }
}