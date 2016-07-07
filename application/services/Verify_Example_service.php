<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 事例Service,只做演示使用
 */
class Verify_Example_service extends SP_Service
{
    const VERIFY_PHONE_TIME = 120;
    const VERIFY_EMAIL_TIME = 120;
    const EMAIL_PROJECT = 'bbb';
    const PHONE_PROJECT = 'aaa';

    const HTTPS_ADDRESS = "http://172.16.101.101:8081/user/active/setEmail?";
    const OVERDUE_TIME = 7200;
    const TINY_URL_API = "http://dwz.cn/create.php";
    const KEY_PREFIX = "email_verify_";
    const PHONE_NUM_PREFIX = "phone_verify_";
    public function __construct()
    {
        parent::__construct();
        $this->load->model("user/user_model");
        $this->load->library("NSQ");
    }

    //短信验证码
    public function verify($phoneNum)
    {
        if (!$this->cache->memcached->is_supported()) {
            return $this->_getResponse("UNKNOWN_ERROR");
        }

        if ($this->cache->memcached->get(self::PHONE_NUM_PREFIX.$phoneNum)) {
            return $this->_getResponse("REPETITIVE_OPERATION");
        }

        $phoneReg = '/^0?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/';
        if (preg_match($phoneReg, $phoneNum)) {
            $userInfo = $this->user_model->findByWhere("phonenumber=? LIMIT 0,1", [$phoneNum]);
            if ($userInfo) {
                return $this->_getResponse("USER_ALREADY_EXISTS");
            }
        }else{
            return $this->_getResponse("PARAM_ERROR");
        }

        $verificationCode = rand(100000,999999);

        $memRes = $this->cache->memcached->save(self::PHONE_NUM_PREFIX.$phoneNum, $verificationCode, self::VERIFY_PHONE_TIME);
        if (!$memRes){
            return $this->_getResponse("UNKNOWN_ERROR");
        }
        $data = json_encode([
            "project" => self::PHONE_PROJECT,
            "multi" => [
                [
                    'to' => $phoneNum,
                    'vars' => [
                        'code' => $verificationCode,
                        'time' => self::VERIFY_PHONE_TIME.'秒'
                    ]
                ]
            ]
        ]);
        $this->nsq->publishTo('localhost')->publish('NSQ-Consumer-message', new nsqphp\Message\Message($data));
        return $this->_getResponse("SUCCESS");
    }

    //邮箱验证
    public function verifyEmail($uid,$email)
    {
        if (!$this->cache->memcached->is_supported()) {
            return $this->_getResponse("UNKNOWN_ERROR");
        }

        $emailReg = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (!preg_match($emailReg, $email)){
            return $this->_getResponse("PARAM_ERROR");
        }

        $userInfo = $this->user_model->findByWhere("email=? LIMIT 0,1", [$email]);
        if ($userInfo) {
            return $this->_getResponse("USER_ALREADY_EXISTS");
        }

        if ($this->cache->memcached->get(self::KEY_PREFIX.$email)) {
            return $this->_getResponse("REPETITIVE_OPERATION");
        }

        $userInfo = $this->user_model->findById($uid);
        $userItem = $userInfo[0];
        $phoneNum = $userItem['phonenumber'];

        //修改邮箱操作？
        if ($userItem['email']){
            //载入模板
            $resStr = $this->template->fetch("page/email/alertEmail.tpl");
            $userName = "亲爱的用户".$phoneNum;
            $data = json_encode([
                "project" => self::EMAIL_PROJECT,
                'to' => [$userItem['email']],
                "params" => [
                    'title' => '修改邮箱',
                    'username' => $userName,
                    'message' => $resStr
                ]
            ]);
            $this->nsq->publishTo('localhost')->publish('NSQ-Consumer-mail', new nsqphp\Message\Message($data));
        }

        $randNum = rand(100000,999999);
        $str = $uid.time().$randNum;
        $verificationCode = md5($str);
        $key = self::KEY_PREFIX.$email;
        $memRes = $this->cache->memcached->save($key, $verificationCode, self::VERIFY_EMAIL_TIME);
        if (!$memRes){
            return $this->_getResponse("UNKNOWN_ERROR");
        }
        $httpVar = 'user='.urlencode($phoneNum).'&email='.urlencode($key).'&verify='.urlencode($verificationCode);
        $httpStr = self::HTTPS_ADDRESS.$httpVar;

        //获取短连接
        $body = array(
            'url' => $httpStr
        );
        $this->mcurl->add_call("getTinyUrl","post",self::TINY_URL_API,$body);
        $strResponses = $this->mcurl->execute();
        $strResponse = $strResponses['getTinyUrl']["response"];
        $arrResponse = json_decode($strResponse,true);
        $url = $arrResponse['tinyurl']."\n";
        if (!empty($arrResponse['err_msg'])){
            $url = $arrResponse['longurl']."\n";
            //return $this->_getResponse("UNKNOWN_ERROR");
        }

        $now = time();
        $overdueTime = $now + self::OVERDUE_TIME;
        $timeStr = date('Y-m-d H:i:s',$overdueTime);
        $data = [
            'url' => $url,
            'timeStr' => $timeStr
        ];

        //载入模板
        $this->template->assign("data",$data);
        $resStr = $this->template->fetch("page/email/email.tpl");
        $userName = "亲爱的用户".$phoneNum;
        $data = json_encode([
            "project" => self::EMAIL_PROJECT,
            'to' => [$email],
            "params" => [
                'title' => '绑定您的账户邮箱',
                'username' => $userName,
                'message' => $resStr
            ]
        ]);
        $this->nsq->publishTo('localhost')->publish('NSQ-Consumer-mail', new nsqphp\Message\Message($data));
        return $this->_getResponse("SUCCESS");
    }

    protected function _getResponse($key, $data = [])
    {
        $maps = [
            "SUCCESS" => ["err" => 0, "msg" => "success", "data" => $data],
            "UNKNOWN_ERROR" => ["err" => 1, "msg" => "unknown error", "data" => $data],
            "REPETITIVE_OPERATION" => ["err" => 2, "msg" => "SMS has been sent", "data" => $data],
            "USER_ALREADY_EXISTS" => ["err" => 3, "msg" => "user already exists", "data" => $data],
            "PARAM_ERROR" => ["err" => 10003, "msg" => "param error", "data" => $data],
        ];

        return $maps[$key];
    }
}
