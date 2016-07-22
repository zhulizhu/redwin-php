<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class OrderController extends HomeController {
	
	//系统首页
    public function index(){
    	
        $category = D('Category')->getTree();
        $lists    = D('Document')->lists(null);

        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页
        $this->display();
    }
    //添加订单
	public function update(){
		//添加或修改
		$allcart = json_decode(cookie('cart'),true);
    	$theObj=$allcart["obj"];
    	sort($theObj);
    	for ($m=0;$m<count($theObj);$m++){
    		$id = $theObj[$m]['id'];
    		$countid = M('Orderlist')->where('pro_id='.$id)->count();
    		$pro = M('Product')->where('id='.$theObj[$m]["id"])->find();
    		$rest = $pro['total']-$countid;
    		if($theObj[$m]["length"]>$rest){
    			echo 'false';
    			exit;
    		}
    	}
		$Address = D('Order');//调用模型
		$res = $Address->update();
		if($res){
			$allcart = json_decode(cookie('cart'),true);
			$theObj = $allcart["obj"];
			$theObj=array_values($theObj);
			for($i=0; $i<count($theObj); $i++){
				$join=M('Product')->where('id='.$theObj[$i]["id"])->getField('join');
				$join += $theObj[$i]['length'];
				M('Product')->where('id='.$theObj[$i]["id"])->setField('join',$join);
			}
			$allcart=null;//订单添加成功后清空cookie
			cookie('cart',json_encode($allcart));
			echo $res;
			exit;	
		}else {
			echo 'false';
			exit;
		}

	}
}