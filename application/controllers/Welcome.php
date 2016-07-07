<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 事例控制器
 */
class Welcome extends SP_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->service("Welcome_service");
	}

	public function index()
	{
		$authorName = $this->welcome_service->getAuthorName();
		$data['authorName'] = $authorName;
		$data['title'] = 'Welcome to Spir';

		$this->smarty->assign("data",$data);
		$this->smarty->display("welcome_module/page/index.tpl");

		//使用curl事例:
//		$response = $this->curl->get('www.spir.com/example/getUserInfo?uid=1');
//		echo $response->body;
	}
}
