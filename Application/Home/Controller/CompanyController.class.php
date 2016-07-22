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
use Admin\Model\AuthGroupModel;

/**
 * 企业用户控制器
 */
class CompanyController extends HomeController {

	private $wechat;
	
	/* 用户中心首页 */
	public function index(){
		if(HUID){
			$user_type = M('Member')->where('uid='.is_login())->find();
			if(!$user_type['user_type']){
				$this->error( '你的账号没有企业用户权限',U('User/login') );
			}
			$this->meta_title = '企业用户中心';
			//获取用户详细信息
			$info = array();
			$info['group'] = AuthGroupModel::getUserGroup(is_login());
			$user = M('Member')->where('uid='.is_login())->find();
			$info['picture'] = $user['picture'];
			$info['last_login_time'] = $user['last_login_time'];
			$info['last_login_ip'] = $user['last_login_ip'];
			$info['score'] = $user['score'];
			$this->assign('info',$info);
				
			//获取用户表字段
			$userfiled = M('Userfiled')->where(array('is_show'=>1))->select();
			$this->assign('userfiled',$userfiled);
			//获取最近发布商品
			$goods = M('document')->where('uid='.is_login())->order("create_time desc")->limit(3)->select();
			$this->assign('goods',$goods);
			//获取企业订单
			$comid = M('Company')->where('uid='.is_login())->find();
			$com_id = $comid['id'];
			$ord = M('Order')->where(array('com_id'=>$com_id,'deletes'=>1,'_logic'=>'and'))->limit(3)->select();
			for($i=0;$i<count($ord);$i++){
				$name = M('orderlist')->where(array('order_id'=>$ord[$i]['id']))->select();
				for($j=0;$j<count($name);$j++){
					$ord[$i][suborder][$j]["id"]=$name[$j]['id'];
					$ord[$i][suborder][$j]["pic"]=$name[$j]['picture'];
					$ord[$i][suborder][$j]["pro_id"]=$name[$j]['pro_id'];
					$ord[$i][suborder][$j]["title"]=$name[$j]['title'];
					$ord[$i][suborder][$j]["price"]=$name[$j]['price'];
					$ord[$i][suborder][$j]["length"]=$name[$j]['length'];
				}
				$addressee =  M('address')->where('id='.$ord[$i]['address_id'])->find();
				$ord[$i][addressee]= $addressee[addressee];
			}
			//获取订单类别数目
			$map=array();
			$map["com_id"]=$com_id;
			$map["deletes"]=1;
			$map['Status'] = 1;
			$counts = $this->lists('Order',$map);
			$ord[counts1]=count($counts);
			$map['Status'] = 2;
			$counts = $this->lists('Order',$map);
			$ord[counts2]=count($counts);
			$map['Status'] = 3;
			$counts = $this->lists('Order',$map);
			$ord[counts3]=count($counts);
			$this->assign('ord',$ord);
			$this->display();
		} else {
			$this->redirect('User/login');
		}
	}
	
	/**
	 * 用户信息
	 * @author huajie <banhuajie@163.com>
	 */
	public function profile(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		if(IS_POST){
			$data = I('post.');
			$username = M('Member')->getField('uid,nickname');
			if(array_search($data[nickname],$username)!=false){
				$this->error("该昵称已存在!请重新输入!");
			}else{
				$data['birthday'] = $data['year'] . "-" . $data['mouth'] . "-" . $data['day'];
				$res = M('Member')->where(array('uid'=>$data['uid']))->save($data);
				if($res){
					$this->success("修改成功!");
				}else{
					$this->error("修改失败!");
				}
			}
		}else{
			$info = M('Member')->where(array('uid'=>is_login()))->find();
			$birthday = explode("-",$info['birthday']);
			$info['year'] = $birthday[0];
			$info['mouth'] = $birthday[1];
			$info['day'] = $birthday[2];
			$this->assign('info',$info);
			$this->display();
		}
	}
	/**
	 * 企业信息
	 * @author huajie <banhuajie@163.com>
	 */
	public function company(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		if(IS_POST){
			$data = I('post.');
			$res = M('Company')->where(array('uid'=>$data['uid']))->save($data);
			if($res){
				$this->success("修改成功!");
			}else{
				$this->error("修改失败!");
			}
		}else{
			$info = M('Company')->where(array('uid'=>is_login()))->find();
			$this->assign('info',$info);
			$this->display();
		}
	}
	/**
	 * 头像设置
	 */
	public function photo(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		if(IS_POST){
			$res = M('Member')->where('uid='.is_login())->setField("picture",$_POST['picture']);
			if($res){
				$this->success("设置照片成功！");
				exit;
			}else{
				$this->error("设置照片失败！");
				exit;
			}
		}
		$photo = M('Member')->where('uid='.is_login())->find();
		$this->assign('photo',$photo);
		$this->display();
	}
	
	/**
	 * 账户安全
	 */
	public function safety(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		if ( IS_POST ) {
			//获取参数
			$uid        =   $_POST['uid'];
			$password   =   I('post.oldpassword');
			$repassword = I('post.repassword');
			$data['password'] = I('post.password');
			empty($password) && $this->error('请输入原密码');
			empty($data['password']) && $this->error('请输入新密码');
			empty($repassword) && $this->error('请输入确认密码');
			 
			if($data['password'] !== $repassword){
				$this->error('您输入的新密码与确认密码不一致');
			}
	
			$Api = new UserApi();
			$res = $Api->updateInfo($uid, $password, $data);
			$u=M('ucenter_member')->where('id='.is_login())->find();
			if($res['status']){
				$this->success("修改成功，请重新登陆！",U('user/login'));
			}else{
				$this->error($res['info']);
			}
		}else{
			$this->display();
		}
		 
	}
	/**
	 * 我的订单
	 *
	 */
	public function order($action = "show"){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		switch ($action){
			case "delete":
				if(IS_GET){
					$id = $_GET['id'];
					$res = M('order')->where('id='.$id)->setField("deletes",0);
					if($res){
						$this->success("删除成功！");
						exit;
					}else{
						$this->error("删除失败！");
						exit;
					}
				}
				break;
			case "deliver":
				if(IS_GET){
					$id = $_GET['id'];
					$res = M('order')->where('id='.$id)->setField("Status",2);
					if($res){
						$this->success("设置发货成功！");
						exit;
					}else{
						$this->error("设置发货失败！");
						exit;
					}
				}
				break;
		}
		$com_id = M('Company')->where('uid='.is_login())->find();
		$map=array();
		$map["com_id"]=$com_id['id'];
		$map["deletes"]=1;
		$t=strtotime("-90 days");
		if($_GET['status']){
			$map['Status'] = $_GET['status'];
		}
		if($_POST[search_order]&&$_POST[search_order]!="请输入要查询的订单编号"){
			$map["id"]=array("like","%".$_POST['search_order']."%");
		}
		if($_POST[order_time]==2){
			$map["create_time"]=array('gt',$t);
		}elseif($_POST[order_time]==3){
			$t=strtotime("-90 days");
			$map["create_time"]=array('elt',$t);
		}else{
			$map=$map;
		}
		$ord = $this->lists('Order',$map);
		for($i=0;$i<count($ord);$i++){
			$name = M('orderlist')->where(array('order_id'=>$ord[$i]['id']))->select();
			for($j=0;$j<count($name);$j++){
				$ord[$i][suborder][$j]["id"]=$name[$j]['id'];
				$ord[$i][suborder][$j]["pic"]=$name[$j]['picture'];
				$ord[$i][suborder][$j]["pro_id"]=$name[$j]['pro_id'];
				$ord[$i][suborder][$j]["title"]=$name[$j]['title'];
				$ord[$i][suborder][$j]["price"]=$name[$j]['price'];
				$ord[$i][suborder][$j]["length"]=$name[$j]['length'];
			}
			$addressee =  M('address')->where('id='.$ord[$i]['address_id'])->find();
			$ord[$i][addressee]= $addressee[addressee];
		}
		$this->assign('ord',$ord);
		$this->display();
	}
	/**
	 * 退换货
	 */
	public function returns($action = "show"){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$user_type = M('Member')->where('uid='.is_login())->find();
		if(!$user_type['user_type']){
			$this->error( '你的账号没有企业用户权限',U('User/login') );
		}
		switch ($action){
			case "delete":
				if(IS_GET){
					$id = $_GET['id'];
					$res = M('returns')->where('id='.$id)->setField("deletes",0);
					if($res){
						$this->success("删除成功！");
						exit;
					}else{
						$this->error("删除失败！");
						exit;
					}
				}
				break;
			case "deliver":
				if(IS_GET){
					$id = $_GET['id'];
					$res = M('returns')->where('id='.$id)->setField("status",2);
					if($res){
						$this->success("设置发货成功！");
						exit;
					}else{
						$this->error("设置发货失败！");
						exit;
					}
				}
				break;
		}
		$pid = M('Company')->where('uid='.is_login())->find();
		$map=array();
		$map["com_id"]=$pid['id'];
		$map["deletes"]=1;
		$t=strtotime("-90 days");
		if($_GET['status']){
			$map['status'] = $_GET['status'];
		}
		if($_POST[search_order]&&$_POST[search_order]!="请输入要查询的退货编号"){
			$map["id"]=array("like","%".$_POST['search_order']."%");
		}
		if($_POST[order_time]==2){
			$map["create_time"]=array('gt',$t);
		}elseif($_POST[order_time]==3){
			$t=strtotime("-90 days");
			$map["create_time"]=array('elt',$t);
		}else{
			$map=$map;
		}
		$ord = $this->lists('returns',$map);
		$this->assign('ord',$ord);
		$this->display();
	}
}
