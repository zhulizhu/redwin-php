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
use Common\Api\ExpressApi;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ApplyController extends AdminController {

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
        			$res = M('Apply')->where('id='.$id)->setField("status",-1);
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
        $map["status"]=1;
        $t=strtotime("-90 days");
        //编号查询
        if(I('search_order')&&$_POST[search_order]!="请输入要查询的报名编号"){
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
       //品牌查询
        if($_POST['brand']){
        	$map["brand"]=$_POST['brand'];
        	$cars = $this->linktwo($_POST['brand']);
        	$this->assign('cars',$cars);
        }
        //车系查询
        if($_POST['cars']){
        	$map["cars"]=$_POST['cars'];
        	$deploy = $this->linkThree($_POST['cars']);
        	$this->assign('deploy',$deploy);
        }
        //配置查询
        if($_POST['deploy']){
        	$map["deploy"]=$_POST['deploy'];
        }
        $group=M('AuthGroupAccess')->where('uid='.is_login())->find();
        if(IS_ROOT){
        	$brand = M('Purcate')->getField('id,title,pid',true);
        }elseif($group['group_id']==5){
        	$where['title']=get_nickname();
        	$brand = M('Purcate')->where($where)->getField('id,title,pid',true);
        	sort($brand);
        	$map["brand"] = $brand[0]['id'];
        }else{
        	 $brand="null";
        }
        $this->assign('brand',$brand);
        $apply = $this->lists('Apply',$map);
        $this->assign('apply',$apply);
        $this->meta_title = '报名信息';
        $this->display();
 
}
    //车系联动
    public function linktwo($title){
    	$where['id'] = $title;
    	$id = M('Purcate')->where($where)->find();
    	$map['category_id']=$id['id'];
    	$map['pid']=0;
    	$cars=  M('Purchase')->where($map)->select();
    	return $cars;
    }
    //配置联动
    public function linkThree($title){
    	$where['id'] = $title;
    	$id = M('Purchase')->where($where)->find();
    	$map['pid']=$id['id'];
    	$map['type']=2;
    	$deploy=  M('Purchase')->where($map)->select();
    	return $deploy;
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
    		case 'deleteapply':
    			$res = M('Apply')->where($map)->setField("status",-1);
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
