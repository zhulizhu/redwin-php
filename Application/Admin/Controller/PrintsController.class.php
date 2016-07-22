<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PrintsController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index($action="show"){
    	$uid = is_login();
    	if ( !$uid ) {
    		$this->error( '您还没有登录',U('User/login') );
    	}
        switch ($action){
        	case "delete":
        		if(IS_GET){
        			$id = $_GET['id'];
        			$res = M('Prints')->where('id='.$id)->setField("status",0);
        			if($res){
        				$this->success("隐藏成功！");
        				exit;
        			}else{
        				$this->error("隐藏失败！");
        				exit;
        			}
        		}
        		break;
        	case "open":
        		if(IS_GET){
        			$id = $_GET['id'];
        			$res = M('Prints')->where('id='.$id)->setField("status",1);
        			if($res){
        				$this->success("显示成功！");
        				exit;
        			}else{
        				$this->error("显示失败！");
        				exit;
        			}
        		}
        		break;
        }
        $prints = $this->lists('Prints');
        
        $this->assign('prints',$prints);
        $this->meta_title = '订单信息';
        $this->display();
    }
    /**
     * 批量删除订单
     */
	public function changeStatus($method=null){
	    	$id = array_unique((array)I('id',0));
	    	$id = is_array($id) ? implode(',',$id) : $id;
	    	if ( empty($id)) {
	    		$this->error('请选择要操作的数据!');
	    	}
	    	$map['id'] =   array('in',$id);
	    	switch ( strtolower($method) ){
	    		case 'deleteprints':
	    			$res = M('Prints')->where($map)->setField("status",-1);
	    			if($res){
	    				$this->success("删除成功！");
	    				exit;
	    			}else{
	    				$this->error("删除失败！");
	    				exit;
	    			}
	    			break;
	    		default:
	    			$this->error('参数非法');
	    	}
	    }
}
