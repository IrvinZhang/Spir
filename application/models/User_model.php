<?php
/**
 * Created by IntelliJ IDEA.
 * User: irvin
 * Date: 16/7/6
 * Time: 上午10:19
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class User_model extends SP_Model{
    protected $_tableName = "spir_user";
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

    }
    public function findById($id)
    {
        $query = $this->db->query("SELECT * FROM $this->_tableName WHERE id=?", [$id]);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }
    public function findByWhere($where, $data)
    {
        $query = $this->db->query("SELECT * FROM $this->_tableName WHERE " . $where, $data);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

}