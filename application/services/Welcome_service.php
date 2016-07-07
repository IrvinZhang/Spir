<?php
/**
 * Created by IntelliJ IDEA.
 * User: irvin
 * Date: 16/7/4
 * Time: 下午9:37
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Welcome_service extends SP_Service{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("welcome_model");
    }
    public function getAuthorName()
    {
        $res = $this->welcome_model->findById();
        return $res;
    }
    public function memcachedExample()
    {
        //使用memcached事例:
        $this->load->driver('cache');
        $this->cache->memcached->save('name', 'irvin', 100);
        $res = $this->cache->memcached->get('name');
        var_dump($res);
    }
}