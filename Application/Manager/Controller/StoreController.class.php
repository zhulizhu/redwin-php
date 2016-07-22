<?php
// +----------------------------------------------------------------------
// | CheeWoPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;

use Think\Storage;

/**
 * 应用商城
 * 
 * @author lihao <lihao@cheewo.com>
 */
class StoreController extends ManagerController {
	public $appid = "1234567890";
	public $secret = "abcdefghijklmn";
	public $url = Server_domain;
	
	public function __construct() {
		parent::__construct ();
		$this->appid = "1234567890";
		$this->secret = "abcdefghijklmn";
		$this->access_token ();
	}
	
	/**
	 * 应用商城
	 * 
	 * @author lihao <lihao@cheewo.com>
	 */
	public function index() {
		$this->meta_title = '应用商城';
		$this->display ();
	}
	
	/**
	 * 模板风格
	 */
	public function demo() {
		$map ['my'] = 1;
		$url = $this->autourl ( 'get_tpl', $map );
		$data = $this->get ( $url );
		$this->assign ( 'list', $data );
		$this->display ();
	}
	
	public function buythis($id = 0){
		if(IS_GET){
			$map['id'] = $id;
			$url = $this->autourl("detailtpl", $map);
			$info = $this->get ( $url );
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	
	/**
	 * 自动填充URL
	 * 
	 * @param unknown $action        	
	 * @param unknown $data        	
	 * @return string
	 */
	public function autourl($action, $map) {
		$map ['access_token'] = session ( 'ACCESS_TOKEN' );
		$url = "Home/Storeapi/" . $action . "@" . Server_domain;
		$url = U ( $url, $map );
		return $url;
	}
	public function post2($url, $data = '', $upload = false) { // 用于自动更新 access_token 避免死循环 post需要php开启 openssl.dll
		$context = stream_context_create ( array (
				'http' => array (
						'method' => 'POST',
						'header' => "Content-type: " . ($upload ? "multipart/form-data" : "application/x-www-form-urlencoded") . "\r\n" . "Content-Length: " . strlen ( $data ) . "\r\n",
						'content' => $data 
				) 
		) );
		return json_decode ( file_get_contents ( $url, false, $context ), true );
	}
	
	/**
	 * get函数
	 * 
	 * @see \Think\Controller::get()
	 */
	public function get($url) {
		$context = stream_context_create ( array (
				'http' => array (
						'method' => 'GET' 
				) 
		) );
		return json_decode ( file_get_contents ( $url, false, $context ), true );
	}
	
	/**
	 * 获取ACCESS_TOKEN临时授权密匙
	 */
	public function access_token() {
		$acess_token = session ( 'ACCESS_TOKEN' );
		if (empty ( $acess_token )) {
			$url = "http://" . $this->url . "/Storeapi/index/grant_type/client_credential/appid/$this->appid/secret/$this->secret.html";
			$data = $this->get ( $url );
			if (array_key_exists ( $data ['errcode'] )) {
				echo "Error key!";
				exit ();
			} else {
				session ( 'ACCESS_TOKEN', $data ['token'] );
			}
		}
	}
}
