<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wechat\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class LikeController extends HomeController {
	
	public function add($id){
		$data['proid'] = $id;
		$data['uid'] = is_login();
		$data['creata_time'] = NOW_TIME;
		M('Like')->add($data);
	}
	
	public function del($id){
		M('Like')->where('id='.$id)->delete();
	}
	
	public function auto($id){
		if(!is_login()){
			echo "请登录";
			exit;
		}
		$map['proid'] = $id;
		$map['uid'] = is_login();
		$result = M('like')->where($map)->getfield("id");
		if($result){
			M('like')->where('id='.$result)->delete();
			echo "取消收藏";
			exit;
		}else{
			$map['creata_time'] = NOW_TIME;
			M('Like')->add($map);
			echo "添加收藏";
			exit;
		}
	}
	
	
}