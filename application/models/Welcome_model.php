<?php
/**
 * Created by IntelliJ IDEA.
 * User: irvin
 * Date: 16/7/4
 * Time: 下午9:57
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Welcome_model extends SP_Model{
    public function __construct()
    {
        parent::__construct();
    }
    public function findById()
    {
        return "Irvin";
    }
}