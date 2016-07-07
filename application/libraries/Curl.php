<?php
/**
 * Created by IntelliJ IDEA.
 * User: irvin
 * Date: 16/7/7
 * Time: 上午12:43
 */

class Curl{
    protected $curl = null;

    public function __construct()
    {
        $this->curl = new anlutro\cURL\cURL();
    }

    public function __call($method, $args)
    {
        $callable = array($this->curl, $method);
        return call_user_func_array($callable, $args);
    }
}