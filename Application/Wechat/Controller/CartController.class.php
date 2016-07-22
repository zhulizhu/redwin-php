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
use Admin\Controller\PublicController;

/**
 * 购物车控制器
 * @author 智网天下科技 http://www.cheewo.com
 *
 */
class CartController extends HomeController {
	
	/**
	 * 更新购物车  添加或删除
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function update($pro_id,$num=1){
		if(!is_login()){//未登陆，不允许加入购物车
			echo "-1";
			exit;
		}
		$where['id'] = $pro_id;
		$data = M('product')->where($where)->field('title,cover_id')->find();
		if(!$data) echo "产品信息有误";
		$data['pro_id'] = $pro_id;
		$data['price'] = auto_price($pro_id);
		$data['num'] = $num;//默认产品数量,可开发为传参数更改数量
		$cart = D('Cart');
		$status = $cart->update($data);
		if($status){
			echo $cart->get_count();
			exit;
		}else{
			echo "添加购物车失败！";
			exit;
		}
	}
	
	public function buynow_cart($pro_id){
		if(!is_login()){//未登陆，不允许加入购物车
			echo "-1";
			exit;
		}
		$where['id'] = $pro_id;
		$data = M('product')->where($where)->field('title,cover_id')->find();
		if(!$data) echo "产品信息有误";
		$data['pro_id'] = $pro_id;
		//秒杀
		$where = array();
		$where['start_time'] = array("lt",NOW_TIME);
		$where['end_time'] = array("gt",NOW_TIME);
		$killid = M("seckill")->where($where)->getField("id");
		$price = auto_price($pro_id);
		if($killid){
			$where = array();
			$where['pid'] = $killid;
			$where['proid'] = $pro_id;
			$killinfo = M("seckill_list")->where($where)->getField("price");
			if($killinfo){
				$price = $killinfo;
			}
		}
		$data['price'] = $price;
		$data['num'] = 1;
		$cart = D('Cart');
		$status = $cart->buynow_cart($data);
		if($status){
			echo $status;
			exit;
		}else{
			echo "添加购物车失败！";
			exit;
		}
	}
	
	
	public function jifen_cart($pro_id){
		if(!is_login()){//未登陆，不允许加入购物车
			echo "-1";
			exit;
		}
		$where['id'] = $pro_id;
		$data = M('product')->where($where)->field('title,cover_id,real_price')->find();
		if(!$data) echo "产品信息有误";
		$data['pro_id'] = $pro_id;
		$data['price'] = $data['real_price'];
		$data['num'] = 1;
		$cart = D('Cart');
		$status = $cart->buynow_cart($data);
		if($status){
			echo $status;
			exit;
		}else{
			echo "添加购物车失败！";
			exit;
		}
	}
	
	
	
}