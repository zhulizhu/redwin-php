<?php

namespace Home\Controller;
use OT\DataDictionary;
use Admin\Controller\PublicController;

/**
 * 应用商店API,仅官方有。其他开发请删除该控制器 
 * 版本：v 14.6.3 开发版
 */
class StoreapiController extends HomeController {
	
	public $grant_type;
	public $appid;
	public $secret;
	
	/**
	 * 构造函数
	 */
	public function __construct(){
		
		$this->grant_type = I('get.grant_type');//获取access_token凭证
		$this->appid = I('get.appid');//应用程序ID
		$this->secret = I('get.secret');//应用程序密匙
	}
	
    public function index(){
    	
    	if($this->grant_type=="client_credential"){
    		$this->get_token();
    	}
    	
    }
    
    /**
     * 用户获取access_token
     */
    public function get_token(){
    	$business = M('business');
    	$where['appid'] = $this->appid;
    	$where['secret'] = $this->secret;
    	$info = $business->where($where)->find();
    	if(count($info)>0){
    		 $data['access_token'] = think_encrypt(rand(1000,9999));
    		 $data['expires_in'] = 7200;
    		 echo json_encode($data);
    	}else{
    		$data['errcode'] = 40013;
    		$data['errmsg'] = "invalid appid";
    		echo json_encode($data);
    	}
    	exit;
    }
    
}