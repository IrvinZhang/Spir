<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 事例接口Service层
 */
class API_Example_service extends SP_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("User_model");
    }
    public function getUserInfoByUid($uid)
    {
        if (empty($uid)) {
            return $this->_getResponse("PARAM_ERROR");
        }
        $userInfo = $this->User_model->findById($uid);
        if(!$userInfo) {
            return $this->_getResponse("UNKNOWN_ERROR");
        }
        return $this->_getResponse("SUCCESS", $userInfo);
    }

    protected function _getResponse($key, $data = [])
    {
        $maps = [
            "SUCCESS" => ["err" => 0, "msg" => "success", "data" => $data],
            "UNKNOWN_ERROR" => ["err" => 1, "msg" => "unknown error", "data" => $data],
            "TOKEN_EXPIRED" => ["err" => 2, "msg" => "token expired", "data" => $data],
            "PARAM_ERROR" => ["err" => 3, "msg" => "param error", "data" => $data],
            "CACHE_ERROR" => ["err" => 4, "msg" => "cache error", "data" => $data],
            "USER_NOT_EXISTS" => ["err" => 5, "msg" => "user not exists", "data" => $data],
        ];

        return $maps[$key];
    }
}