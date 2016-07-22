<?php
// +----------------------------------------------------------------------
// | CheeWoPHP   成都智网天下科技有限公司
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

class VerifyController extends AdminController {
	
	public function  index(){
	
	if(!is_login()){// 还没登录 跳转到登录页面
		$this->redirect('Public/login');
	}	
	$this->meta_title = '验证信息';
	$this->display();
	}
	public function  verify(){
		$num = $_GET['verifycode'];
		if($num){
			$result = M('Verifycode')->where('number='.$num)->find();
			if($result){
				if($result['status']>0){
					$result['status']--;
					M('Verifycode')->where('number='.$num)->setField('status',$result['status']);
					echo "验证成功";
					exit();	
				}elseif($result['status']==0){
					echo "验证失败";
					exit();
					
				}else{
					echo "验证码已删除";
					exit();
				}
			}else{
				echo "验证码无效";
				exit();
			}
		}
	}
	
}