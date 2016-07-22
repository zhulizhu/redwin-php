<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;
use Common\Controller\Wechat;
use Think\AjaxPage;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserhandleController extends HomeController {
	
	
	public function index(){
		$this->display('userhandle');
	}
	/* 用户订单处理首页 */
	public function userhandle(){
		$map['status']=1;
		$time = date("Y/m/d H:i:s",strtotime("-10 minutes",NOW_TIME));
		$map['create_time']=array('lt',$time);
		$order = M('Order')->where($map)->getField('id',true);
		$orders = '('.implode(',', $order).')';
		$array = M()->query("select pro_id,sum(length) as acount from cw_orderlist where order_id in ".$orders." group by pro_id");
		for($i=0;$i<count($array);$i++){
			$join = M('product')->where('id='.$array[$i]['pro_id'])->getField('join');
			$newjoin = $join-$array[$i]['acount'];
			M('product')->where('id='.$array[$i]['pro_id'])->setField('join',$newjoin);
		}
		M('Order')->where($map)->setField('status',-1);
		echo 'ok';
	}


}
