<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;


/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class VerifycodeController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index($action="show"){
    	
    if(!is_login()){// 还没登录 跳转到登录页面
    	$this->redirect('Public/login');
    }
        switch ($action){
        	case "delete":
        		if(IS_GET){
        			$id = $_GET['id'];
        			$res = M('Verifycode')->where('id='.$id)->setField("status",-1);
        			if($res){
        				$this->success("删除成功！");
        				exit;
        			}else{
        				$this->error("删除失败！");
        				exit;
        			}
        		}
        		
        		break;
        }
        $map=array();
        $map["status"]=array('neq',-1);
        $t=strtotime("-90 days");
        //编号查询
        if(I('search_order')&&$_POST[search_order]!="请输入要查询的验证码编号"){
        	$map["id"]=array("like","%".I('search_order')."%");
        }
        //时间查询
        if($_POST[order_time]==2){
        	$map["create_time"]=array('gt',$t);
        }elseif($_POST[order_time]==3){
        	$map["create_time"]=array('elt',$t);
        }else{
        	$map=$map;
        }
        $verifycode = $this->lists('Verifycode',$map);
        $this->assign('verifycode',$verifycode);
        $this->meta_title = '验证码信息';
        $this->display();
 
}
  
    /**
     * 批量删除
     */
    public function changeStatus($method=null){
    	$id = array_unique((array)I('id',0));
    	$id = is_array($id) ? implode(',',$id) : $id;
    	if ( empty($id)) {
    		$this->error('请选择要操作的数据!');
    	}
    	$map['id'] =   array('in',$id);
    	switch ( strtolower($method) ){
    		case 'deleteverify':
    			$res = M('Verifycode')->where($map)->setField("status",-1);
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
