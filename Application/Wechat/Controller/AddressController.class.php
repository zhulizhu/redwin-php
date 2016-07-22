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
class AddressController extends HomeController {
	
	//系统首页
    public function index(){
    	
        $category = D('Category')->getTree();
        $lists    = D('Document')->lists(null);

        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页

        
                 
        $this->display();
    }
    //添加收货地址
	public function update(){
		//添加或修改
		$Address = D('Address');//调用模型
		$res = $Address->update();
		if($res){
			$this->success('成功');
		}else {
			$this->error('错误');
		}
// 		//删除
// 		$map['uid'] = 1;
// 		$result = M('Address')->where($map)->delete();
// 		//查询
// 		$map['status'] = array('gt'=>1);
// 		$result = M('Address')->where($map)->select();
	}
}