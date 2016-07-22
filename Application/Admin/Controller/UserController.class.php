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
 * 
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class UserController extends AdminController {
	
	/**
	 * 用户管理首页
	 * 
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function index() {
		$nickname = I ( 'nickname' );
		$map ['status'] = array (
				'egt',
				0 
		);
		$map ['uid'] = array (
				'gt',
				1 
		); // 排除网站创始人
		
		if (isset ( $_GET ['uid'] )) {
			$map ['uid'] = $_GET ['uid'];
		}
		if (isset ( $_GET ['nickname'] )) {
			$map ['nickname'] = array (
					'like',
					'%' . $_GET ['nickname'] . '%' 
			);
		}
		
		if (isset ( $_GET ['mobile'] )) {
			$mobile_where ['mobile'] = array (
					"like",
					'%' . $_GET ['mobile'] . '%' 
			);
			$uids = M ( 'ucenter_member' )->where ( $mobile_where )->getField ( "id", true );
			$map ['uid'] = array (
					"in",
					implode ( ",", $uids ) 
			);
		}
		
		
		if(isset($_GET['time-start']) && isset($_GET['time-end'])){
			$uidwhere['reg_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
			$uidss = M('ucenter_member')->where($uidwhere)->getField("id",true);
			$map['uid'] = array("in",implode(",",$uidss));
		}
		
		if (groupid!=null && groupid > 11 && groupid < 14) {
			$where ['puid'] = is_login ();
			$thislist = M ( 'AuthGroupAccess' )->where ( $where )->getField ( 'uid', true );
			$map = array ();
			$map ['uid'] = array (
					"in",
					implode ( ",", $thislist ) 
			);
		}
		if (isset ( $_GET ['group'] )) { // 分组查询
			$group_id = I ( 'get.group' );
			$thislist = M ( 'AuthGroupAccess' )->where ( 'group_id=' . $group_id )->getField ( 'uid', true );
			if ($thislist == null) {
				$list = null;
			} else {
				$thislist = implode ( ',', $thislist );
				$map ['uid'] = array (
						'in',
						$thislist 
				);
				$list = $this->lists ( 'Member', $map );
			}
			$this->assign ( 'thisgroup', I ( 'get.group' ) );
		} else {
			$list = $this->lists ( 'Member', $map );
		}
		int_to_string ( $list );
		if (! $list) {
			$list = array ();
		}
		for($i = 0; $i < count ( $list ); $i ++) {
			$info = M ( 'ucenter_member' )->where ( "id=" . $list [$i] ['uid'] )->field ( "mobile,headimgurl,status" )->find ();
			$list [$i] ['status'] = $info ['status'];
			$list [$i] ['mobile'] = $info ['mobile'];
			$list [$i] ['headimgurl'] = $info ['headimgurl'];
			$group = M ( 'AuthGroupAccess' )->where ( 'uid=' . $list [$i] ['uid'] )->find ();
			if ($group ['puid']) {
				$list [$i] ['upgroup'] = get_group_title_by_uid ( $group ['puid'] ) . ":<a href='".U('index?uid='.$group['puid'])."'>" . get_nickname ( $group ['puid'] ).'</a>';
			} else {
				$list [$i] ['upgroup'] = "暂无上级";
			}
			
			$group = M ( 'AuthGroup' )->where ( 'id=' . $group ['group_id'] )->find ();
			$group = $group ['title'];
			if (empty ( $group )) {
				$group = "未分组";
			}
			$list [$i] ['group'] = $group;
			
		}
		
		/* 获取分组信息 */
		$grouplist = M ( 'AuthGroup' )->select ();
		$this->assign ( 'grouplist', $grouplist );
		
		if (is_login () == 1) {
			$groupid = 15;
		} else {
			$groupid = groupid;
		}
		session('where',$map);
		$this->assign ( "nowuid", is_login () );
		$this->assign ( "nowgroupid", $groupid );
		$this->assign ( '_list', $list );
		
		$countmoney = M("member")->Sum("money");
		$this->assign("countmoney",$countmoney);
		
		$this->meta_title = '用户信息';
		$this->display ();
	}
	
	
	public function youfen() {
		$nickname = I ( 'nickname' );
		$map ['status'] = array (
				'egt',
				0
		);
		$map ['uid'] = array (
				'gt',
				1
		); // 排除网站创始人
	
		if (isset ( $_GET ['uid'] )) {
			$map ['uid'] = $_GET ['uid'];
		}
		if (isset ( $_GET ['nickname'] )) {
			$map ['nickname'] = array (
					'like',
					'%' . $_GET ['nickname'] . '%'
			);
		}
	
		if (isset ( $_GET ['mobile'] )) {
			$mobile_where ['mobile'] = array (
					"like",
					'%' . $_GET ['mobile'] . '%'
			);
			$uids = M ( 'ucenter_member' )->where ( $mobile_where )->getField ( "id", true );
			$map ['uid'] = array (
					"in",
					implode ( ",", $uids )
			);
		}
	
	
		if(isset($_GET['time-start']) && isset($_GET['time-end'])){
			$uidwhere['reg_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
			$uidss = M('ucenter_member')->where($uidwhere)->getField("id",true);
			$map['uid'] = array("in",implode(",",$uidss));
		}
	
		if (groupid!=null && groupid > 11 && groupid < 14) {
			$where ['puid'] = is_login ();
			$thislist = M ( 'AuthGroupAccess' )->where ( $where )->getField ( 'uid', true );
			$map = array ();
			$map ['uid'] = array (
					"in",
					implode ( ",", $thislist )
			);
		}
		if (isset ( $_GET ['group'] )) { // 分组查询
			$group_id = I ( 'get.group' );
			$thislist = M ( 'AuthGroupAccess' )->where ( 'group_id=' . $group_id )->getField ( 'uid', true );
			if ($thislist == null) {
				$list = null;
			} else {
				$thislist = implode ( ',', $thislist );
				$map ['uid'] = array (
						'in',
						$thislist
				);
				$list = $this->lists ( 'Member', $map );
			}
			$this->assign ( 'thisgroup', I ( 'get.group' ) );
		} else {
			$list = $this->lists ( 'Member', $map ,"youfen desc" );
		}
		int_to_string ( $list );
		if (! $list) {
			$list = array ();
		}
		for($i = 0; $i < count ( $list ); $i ++) {
			$info = M ( 'ucenter_member' )->where ( "id=" . $list [$i] ['uid'] )->field ( "mobile,headimgurl,status" )->find ();
			$list [$i] ['status'] = $info ['status'];
			$list [$i] ['mobile'] = $info ['mobile'];
			$list [$i] ['headimgurl'] = $info ['headimgurl'];
			$group = M ( 'AuthGroupAccess' )->where ( 'uid=' . $list [$i] ['uid'] )->find ();
			if ($group ['puid']) {
				$list [$i] ['upgroup'] = get_group_title_by_uid ( $group ['puid'] ) . ":<a href='".U('index?uid='.$group['puid'])."'>" . get_nickname ( $group ['puid'] ).'</a>';
			} else {
				$list [$i] ['upgroup'] = "暂无上级";
			}
				
			$group = M ( 'AuthGroup' )->where ( 'id=' . $group ['group_id'] )->find ();
			$group = $group ['title'];
			if (empty ( $group )) {
				$group = "未分组";
			}
			$list [$i] ['group'] = $group;
				
		}
	
		/* 获取分组信息 */
		$grouplist = M ( 'AuthGroup' )->select ();
		$this->assign ( 'grouplist', $grouplist );
	
		if (is_login () == 1) {
			$groupid = 15;
		} else {
			$groupid = groupid;
		}
		session('where',$map);
		$this->assign ( "nowuid", is_login () );
		$this->assign ( "nowgroupid", $groupid );
		$this->assign ( '_list', $list );
	
		$countmoney = M("member")->Sum("money");
		$this->assign("countmoney",$countmoney);
	
		$this->meta_title = '用户信息';
		$this->display ();
	}

	public function jifen() {
		$nickname = I ( 'nickname' );
		$map ['status'] = array (
			'egt',
			0
		);
		$map ['uid'] = array (
			'gt',
			1
		); // 排除网站创始人

		if (isset ( $_GET ['uid'] )) {
			$map ['uid'] = $_GET ['uid'];
		}
		if (isset ( $_GET ['nickname'] )) {
			$map ['nickname'] = array (
				'like',
				'%' . $_GET ['nickname'] . '%'
			);
		}

		if (isset ( $_GET ['mobile'] )) {
			$mobile_where ['mobile'] = array (
				"like",
				'%' . $_GET ['mobile'] . '%'
			);
			$uids = M ( 'ucenter_member' )->where ( $mobile_where )->getField ( "id", true );
			$map ['uid'] = array (
				"in",
				implode ( ",", $uids )
			);
		}


		if(isset($_GET['time-start']) && isset($_GET['time-end'])){
			$uidwhere['reg_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
			$uidss = M('ucenter_member')->where($uidwhere)->getField("id",true);
			$map['uid'] = array("in",implode(",",$uidss));
		}

		if (groupid!=null && groupid > 11 && groupid < 14) {
			$where ['puid'] = is_login ();
			$thislist = M ( 'AuthGroupAccess' )->where ( $where )->getField ( 'uid', true );
			$map = array ();
			$map ['uid'] = array (
				"in",
				implode ( ",", $thislist )
			);
		}
		if (isset ( $_GET ['group'] )) { // 分组查询
			$group_id = I ( 'get.group' );
			$thislist = M ( 'AuthGroupAccess' )->where ( 'group_id=' . $group_id )->getField ( 'uid', true );
			if ($thislist == null) {
				$list = null;
			} else {
				$thislist = implode ( ',', $thislist );
				$map ['uid'] = array (
					'in',
					$thislist
				);
				$list = $this->lists ( 'Member', $map );
			}
			$this->assign ( 'thisgroup', I ( 'get.group' ) );
		} else {
			$list = $this->lists ( 'Member', $map ,"jifen desc" );
		}
		int_to_string ( $list );
		if (! $list) {
			$list = array ();
		}
		for($i = 0; $i < count ( $list ); $i ++) {
			$info = M ( 'ucenter_member' )->where ( "id=" . $list [$i] ['uid'] )->field ( "mobile,headimgurl,status" )->find ();
			$list [$i] ['status'] = $info ['status'];
			$list [$i] ['mobile'] = $info ['mobile'];
			$list [$i] ['headimgurl'] = $info ['headimgurl'];
			$group = M ( 'AuthGroupAccess' )->where ( 'uid=' . $list [$i] ['uid'] )->find ();
			if ($group ['puid']) {
				$list [$i] ['upgroup'] = get_group_title_by_uid ( $group ['puid'] ) . ":<a href='".U('index?uid='.$group['puid'])."'>" . get_nickname ( $group ['puid'] ).'</a>';
			} else {
				$list [$i] ['upgroup'] = "暂无上级";
			}

			$group = M ( 'AuthGroup' )->where ( 'id=' . $group ['group_id'] )->find ();
			$group = $group ['title'];
			if (empty ( $group )) {
				$group = "未分组";
			}
			$list [$i] ['group'] = $group;

		}

		/* 获取分组信息 */
		$grouplist = M ( 'AuthGroup' )->select ();
		$this->assign ( 'grouplist', $grouplist );

		if (is_login () == 1) {
			$groupid = 15;
		} else {
			$groupid = groupid;
		}
		session('where',$map);
		$this->assign ( "nowuid", is_login () );
		$this->assign ( "nowgroupid", $groupid );
		$this->assign ( '_list', $list );

		$countmoney = M("member")->Sum("money");
		$this->assign("countmoney",$countmoney);

		$this->meta_title = '用户信息';
		$this->display ();
	}
	
	public function youfen_detail($uid){
		
		$map = array();
		$map['puid'] = $uid;
		
		if(isset($_GET['yf_type'])){
			$map['yf_type'] = I("get.yf_type");
		}
		
		if(isset($_GET['time-start']) && isset($_GET['time-end'])){
			$map['create_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
		}
		
		$list = $this->lists("youfen",$map);
		$this->assign("list",$list);
		
		$info = M("member")->where("uid=".$uid)->field("uid,youfen")->find();
		$this->assign("info",$info);
		$this->display();
	}
	
	public function youfen_kouchu(){
		if(IS_POST){
			$data = I("post.");
			$id = M("Member")->where("uid=".$data['uid'])->setDec("youfen",$data['how']);
			if($id){
				$newdata = array();
				$newdata['uid'] = 0;
				$newdata['puid'] = $data['uid'];
				$newdata['yf_type'] = 2;
				$newdata['val'] = $data['how'];
				$newdata['create_time'] = NOW_TIME;
				M("youfen")->add($newdata);
				$this->success("扣除成功！");
			}else{
				$this->error("扣除失败！");
			}
		}
		
	}
	
	/**
	 * 导出预约列表
	 * @param string $method
	 * @author 智网天下科技 http://www.cheewo.com
	 * @作者：安彦飞
	 */
	public function exportRadio($outId=null){
	    $order=M("Member")->where(session('where'))->order('uid desc')->select();
	    for($i=0;$i<count($order);$i++){
	        $doc_info[$i]['user'] = get_nickname($order[$i]['uid']);
	        $status[$i]['status'] = M ( 'ucenter_member' )->where ( "id=" . $order [$i] ['uid'] )->getField( "status" );
	        $mobile[$i]['mobile'] = M ( 'ucenter_member' )->where ( "id=" . $order [$i] ['uid'] )->getField("mobile");
	        $group[$i]['puid'] = M ( 'AuthGroupAccess' )->where ( 'uid=' . $order [$i] ['uid'] )->getField("puid");
	        $groupid[$i]['group_id'] = M('AuthGroupAccess')->where( 'uid=' . $order [$i] ['uid'] )->getField("group_id");
	        $title[$i]['title'] = M('AuthGroup')->where('id='.$groupid[$i]['group_id'])->getField("title");
	    }
	    
	    for($k=0;$k<count($order);$k++){
	        $order[$k]['username'] = $doc_info[$k]['user'];
	        $order[$k] ['status'] = $status[$k]['status'];
	        $order[$k] ['mobile'] = $mobile[$k]['mobile'];
	        if ($group[$k]['puid']) {
	            $order [$k] ['upgroup'] = get_group_title_by_uid ( $group[$k]['puid'] ) . ":" . get_nickname ( $group[$k]['puid'] ).",UID:".$group[$k]['puid'];
	        } else {
	            $order [$k] ['upgroup'] = "暂无上级";
	        }
	        $order[$k]['gryj'] = get_yeji_by_uid($order[$k]['uid']);
	        $order[$k]['tdyj'] = get_yeji($order[$k]['uid']);
	        $order[$k]['status'] = get_status_title($order[$k]['status']);
	        $order[$k]['title'] = $title[$k]['title'];
	    }
	    
	    
	    $file_name =date('Y-m-d H:i:s',time())." 用户信息";
	    header('Content-Type: text/xls');
	    header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
	    $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
	    header('Content-Disposition: attachment;filename="' .$str . '.xls"');
	    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	    header('Expires:0');
	    header('Pragma:public');
	    
	    $group_id = M('auth_group_access')->where("uid=".is_login())->getField("group_id");
	    	
	    $table_data = "<table style='font-size:14px;' border=1px;>";
	    $table_data .="<tr style='font-size:16px;'>";
	    $table_data .='<td style="background:yellow;">UID</td>';
	    $table_data .='<td style="background:yellow;">昵称</td>';
	    $table_data .='<td style="background:yellow;">电话</td>';
	    $table_data .='<td style="background:yellow;">用户组</td>';
	    $table_data .='<td style="background:yellow;">上级分组</td>';
	    
	    if($group_id!=17){
	    	$table_data .='<td style="background:yellow;">我的余额</td>';
	    	$table_data .='<td style="background:yellow;">业务积分</td>';
	    	$table_data .='<td style="background:yellow;">个人业绩</td>';
	    	$table_data .='<td style="background:yellow;">团队业绩</td>';
	    	$table_data .='<td style="background:yellow;">状态</td>';
	    	$table_data .='<td style="background:yellow;">最后登录时间</td>';
	    }
	    $table_data .="</tr>";
	    for($i=0;$i<count($order);$i++){
	        $table_data .="<tr>";
	        $table_data .="<td>".$order[$i]["uid"]."</td>";
	        $table_data .="<td>".$order[$i]["username"]."</td>";
	        $table_data .="<td>".$order[$i]["mobile"]."</td>";
	        if($order[$i]["title"]==null){
	            $table_data .="<td>"."未分组"."</td>";
	        }else{
	            $table_data .="<td>".$order[$i]["title"]."</td>";
	        }
	        $table_data .="<td>".$order[$i]["upgroup"]."</td>";
	        if($group_id!=17){
	        	$table_data .="<td>"."￥".$order[$i]["money"]."</td>";
	        	$table_data .="<td>"."￥".$order[$i]["xxmoney"]."</td>";
	        	$table_data .="<td>".$order[$i]["gryj"]."</td>";
	        	$table_data .="<td>".$order[$i]["tdyj"]."</td>";
	        	$table_data .="<td>".$order[$i]["status"]."</td>";
	        	$table_data .="<td>".time_format($order[$i]["last_login_time"])."</td>";
	        }
	        $table_data .="</tr>";
	    }
	    $table_data.="</table>";
	    echo $table_data;
	    die();
	}
	
	
	public function kouchu(){
		
		$data = I('post.');
		$money = M('Member')->where("uid=".$data['uid'])->getField("xxmoney");
		$money = $money - $data['money'];
		M('Member')->where("uid=".$data['uid'])->setField("xxmoney",$money);
		
		$money_data['uid'] = $data['uid'];
		$money_data['money'] = $data['money'];
		$money_data['puid'] = 0;
		$money_data['add_time'] = NOW_TIME;
		$money_data['money_type'] = 4;
		M('MoneyLog')->add($money_data);
		
		$this->success("扣除成功！");
		
	}
	
	public function cshpaypass($uid){
		M('ucenter_member')->where("id=".$uid)->setField("paypass","");
		$this->success("初始化成功！");
	}
	
	public function test() {
		$auth = get_team ( 193 ); // 先获取团队信息
		$price = array ();
		dump($auth);
		foreach ( $auth as $val ) {
			$price[] = $temp = get_yeji_by_uid($val);
			dump($temp);
		}
		$count = array_sum ( $price );
		dump($count);
		exit;
	}
	public function clrdata() {
		$map ['puid'] = array (
				"neq",
				0 
		);
		$list = M ( 'auth_group_access' )->where ( $map )->select ();
		foreach ( $list as $val ) {
			$count = M ( 'ucenter_member' )->where ( "id=" . $val ['puid'] )->count ();
			if ($count == 0) {
				M ( 'auth_group_access' )->where ( "puid=" . $val ['puid'] )->delete ();
				M ( 'member' )->where ( "uid=" . $val ['puid'] )->delete ();
				echo "发现用户:" . $val ['puid'] . "不存在，已删除垃圾数据！<br>";
			}
		}
		exit ();
	}
	public function team($uid) {
		$where ['puid'] = $uid;
		$thislist = M ( 'AuthGroupAccess' )->where ( $where )->getField ( 'uid', true );
		$map ['uid'] = array (
				"in",
				implode ( ",", $thislist ) 
		);
		$list = $this->lists ( 'Member', $map );
		if ($list) {
			int_to_string ( $list );
			
			for($i = 0; $i < count ( $list ); $i ++) {
				$info = M ( 'ucenter_member' )->where ( "id=" . $list [$i] ['uid'] )->field ( "mobile,headimgurl,status" )->find ();
				$list [$i] ['status'] = $info ['status'];
				$list [$i] ['mobile'] = $info ['mobile'];
				$list [$i] ['headimgurl'] = $info ['headimgurl'];
				$group = M ( 'AuthGroupAccess' )->where ( 'uid=' . $list [$i] ['uid'] )->find ();
				if ($group ['puid']) {
					$list [$i] ['upgroup'] = get_group_title_by_uid ( $group ['puid'] ) . ":<a href='".U('index?uid='.$group['puid'])."'>" . get_nickname ( $group ['puid'] ).'</a>';
				} else {
					$list [$i] ['upgroup'] = "暂无上级";
				}
				
				$group = M ( 'AuthGroup' )->where ( 'id=' . $group ['group_id'] )->find ();
				$group = $group ['title'];
				if (empty ( $group )) {
					$group = "未分组";
				}
				$list [$i] ['group'] = $group;
			}
		} else {
			$list = array ();
		}
		
		/* 获取分组信息 */
		$grouplist = M ( 'AuthGroup' )->select ();
		$this->assign ( 'grouplist', $grouplist );
		
		$this->assign ( '_list', $list );
		$this->meta_title = '用户信息';
		$this->display ( "index" );
	}
	
	/**
	 * 编辑页面初始化
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function edit() {
		$id = I ( 'get.id', '' );
		if (empty ( $id )) {
			$this->error ( '参数不能为空！' );
		}
		// 用户表单模型信息
		$model = M ( 'Usermodel' )->where ( 'id=2' )->find ();
		$this->assign ( 'model', $model );
		// 用户表单字段信息
		$fields = $this->get_userfiled ( $model ['id'] );
		$this->assign ( 'fields', $fields );
		// 获取用户信息
		/* 调用用户接口 */
		$User = new UserApi ();
		
		$data = $User->infoall ( $id );
		
		$data = array_merge ( $data, M ( 'member' )->where ( 'uid=' . $id )->find () );
		$this->assign ( 'data', $data );
		
		// 获取用户分组
		$auth_group = M ( 'AuthGroup' )->where ( 'status=1' )->select ();
		$thisgroup = M ( 'AuthGroupAccess' )->where ( 'uid=' . $id )->find ();
		$this->assign("zp",$thisgroup['zp']);
		$jibie = $thisgroup ['puid'];
		$this->assign ( "jibie", $jibie );
		$thisgroup = $thisgroup ['group_id'];
		
		$this->assign ( 'auth_group', $auth_group );
		$this->assign ( 'thisgroup', $thisgroup );
		
		// 获取级别归属信息
		if ($jibie == 0) {
			$jibieinfo = "暂无级别归属";
		} else {
			$where ['id'] = $jibie;
			$userinfo = M ( 'UcenterMember' )->where ( $where )->find ();
			$group_info = get_group_by_uid ( $userinfo ['id'] );
			$jibieinfo = $group_info ['title'] . "：" . $userinfo ['username'] . "&nbsp;UID:".$jibie."&nbsp;电话：" . $userinfo ['mobile'];
		}
		$this->assign ( "jibieinfo", $jibieinfo );
		
		$this->meta_title = '编辑用户';
		$this->display ();
	}
	
	/**
	 * 查找用户
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function likeuser() {
		$where = I ( 'post.' );
		
		if ($where ['group'] == 0) {
			$list = M ( 'AuthGroupAccess' )->getField ( "uid", true );
		} else {
			$where_auth ['group_id'] = $where ['group'];
			$list = M ( 'AuthGroupAccess' )->where ( $where_auth )->getField ( "uid", true );
		}
		if ($where ['like'] == "") {
			$where_user ['id'] = array (
					"in",
					implode ( ",", $list ) 
			);
			$theArray = M ( 'UcenterMember' )->where ( $where_user )->select ();
		} else {
			$where_user ['id'] = array (
					"in",
					implode ( ",", $list ) 
			);
			$where_user ['mobile'] = array (
					"like",
					"%" . $where ['like'] . "%" 
			);
			$theArray = M ( 'UcenterMember' )->where ( $where_user )->select ();
		}
		
		if (count ( $theArray ) > 0) {
			$str = "";
			for($i = 0; $i < count ( $theArray ); $i ++) {
				$group_info = get_group_by_uid ( $theArray [$i] ['id'] );
				$str .= "<tr>";
				$str .= "<td>" . $theArray [$i] ['id'] . "</td>";
				$str .= "<td>" . $theArray [$i] ['username'] . "</td>";
				$str .= "<td>" . $group_info ['title'] . "</td>";
				$str .= "<td>" . $theArray [$i] ['mobile'] . "</td>";
				$str .= "<td><a href='javascript:;' title='" . $theArray [$i] ['id'] . "' jibie='" . $group_info ['title'] . "' value='" . $theArray [$i] ['username'] . "' tel='" . $theArray [$i] ['mobile'] . "' class='selthis'>选择</a></td>";
				$str .= "</tr>";
			}
			echo $str;
			exit ();
		} else {
			echo "";
			exit ();
		}
	}
	
	/**
	 * 更新用户数据
	 */
	public function update() {
		
		// 获取参数
		$UID = I ( 'id' ); // 用户ID
		$data ['nickname'] = I ( 'nickname' ); // 用户名
		empty ( $data ['nickname'] ) && $this->error ( '请输入用户名' );
		$group = I ( 'auth_group' ); // 用户组
		empty ( $group ) && $this->error ( '请选择用户组' );
		$data ['mobile'] = I ( 'mobile' ); // 手机
		                               
		// 更新基础数据
		$Api = new UserApi ();
		$res = $Api->update ( $UID, $data );
		if (! $res ['status']) {
			$this->error ( '更新基础信息出错！' );
		}
		// 更新分组
		$jibie = I ( 'jibie' );
		// empty($jibie) && $this->error('请选择级别归属！');
		$thisgroup = M ( 'AuthGroupAccess' )->where ( 'uid=' . $UID )->find ();
		
		$Auth_group ['group_id'] = $group;
		$Auth_group ['uid'] = $UID;
		$Auth_group ['puid'] = $jibie;
		if(isset($_REQUEST['zp'])){
			$Auth_group['zp'] = $_REQUEST['zp'];
		}

		if (empty ( $thisgroup )) { // 没有分组
			$result = M ( 'AuthGroupAccess' )->add ( $Auth_group );
			if (! $result) {
				$this->error ( '添加分组失败！' );
			}
		} else { // 已有分组
			$result = M ( 'AuthGroupAccess' )->where ( 'uid=' . $UID )->save ( $Auth_group );
			if ($result === false) {
				$this->error ( '更新分组失败' );
			}
		}
		// 更新扩展数据
		$member = D ( 'Member' );
		$result = $member->update ( I ( 'post.' ) );
		if ($result !== false) {
			$this->success ( "更新成功", U ( 'User/index' ) );
		}
	}
	public function deluser($id) {
		M ( 'ucenter_member' )->where ( 'id=' . $id )->delete ();
		M ( 'member' )->where ( 'uid=' . $id )->delete ();
		M ( 'auth_group_access' )->where ( "uid=" . $id )->delete ();
		
		M("youfen")->where("uid=".$id)->delete();
		
		$this->success ( "删除成功！", U ( 'User/index' ) );
	}
	public function clr($uid) {
		M ( 'auth_group_access' )->where ( 'uid=' . $uid )->setField ( "puid", 0 );
		$this->success ( "清除成功！" );
	}
	
	/**
	 * 修改昵称初始化
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function updateNickname() {
		$group = get_group ();
		if ($group == 5) {
			$this->error ( '您没有修改权限！' );
		} else {
			$nickname = M ( 'Member' )->getFieldByUid ( UID, 'nickname' );
			$this->assign ( 'nickname', $nickname );
			$this->meta_title = '修改昵称';
			$this->display ();
		}
	}
	
	/**
	 * 修改昵称提交
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function submitNickname() {
		// 获取参数
		$nickname = I ( 'post.nickname' );
		$password = I ( 'post.password' );
		empty ( $nickname ) && $this->error ( '请输入昵称' );
		empty ( $password ) && $this->error ( '请输入密码' );
		
		// 密码验证
		$User = new UserApi ();
		$uid = $User->login ( UID, $password, 4 );
		($uid == - 2) && $this->error ( '密码不正确' );
		
		$Member = D ( 'Member' );
		$data = $Member->create ( array (
				'nickname' => $nickname 
		) );
		if (! $data) {
			$this->error ( $Member->getError () );
		}
		
		$res = $Member->where ( array (
				'uid' => $uid 
		) )->save ( $data );
		
		if ($res) {
			$user = session ( 'user_auth' );
			$user ['username'] = $data ['nickname'];
			session ( 'user_auth', $user );
			session ( 'user_auth_sign', data_auth_sign ( $user ) );
			$this->success ( '修改昵称成功！' );
		} else {
			$this->error ( '修改昵称失败！' );
		}
	}
	
	/**
	 * 修改密码初始化
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function updatePassword() {
		$this->meta_title = '修改密码';
		$this->display ();
	}
	
	/**
	 * 修改密码提交
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function submitPassword() {
		// 获取参数
		$password = I ( 'post.old' );
		empty ( $password ) && $this->error ( '请输入原密码' );
		$data ['password'] = I ( 'post.password' );
		empty ( $data ['password'] ) && $this->error ( '请输入新密码' );
		$repassword = I ( 'post.repassword' );
		empty ( $repassword ) && $this->error ( '请输入确认密码' );
		
		if ($data ['password'] !== $repassword) {
			$this->error ( '您输入的新密码与确认密码不一致' );
		}
		
		$Api = new UserApi ();
		$res = $Api->updateInfo ( UID, $password, $data );
		if ($res ['status']) {
			$this->success ( '修改密码成功！' );
		} else {
			$this->error ( $res ['info'] );
		}
	}
	
	/**
	 * 用户行为列表
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function action() {
		// 获取列表数据
		$Action = M ( 'Action' )->where ( array (
				'status' => array (
						'gt',
						- 1 
				) 
		) );
		$list = $this->lists ( $Action );
		int_to_string ( $list );
		// 记录当前列表页的cookie
		Cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
		
		$this->assign ( '_list', $list );
		$this->meta_title = '用户行为';
		$this->display ();
	}
	
	/**
	 * 新增行为
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function addAction() {
		$this->meta_title = '新增行为';
		$this->assign ( 'data', null );
		$this->display ( 'editaction' );
	}
	
	/**
	 * 编辑行为
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function editAction() {
		$id = I ( 'get.id' );
		empty ( $id ) && $this->error ( '参数不能为空！' );
		$data = M ( 'Action' )->field ( true )->find ( $id );
		
		$this->assign ( 'data', $data );
		$this->meta_title = '编辑行为';
		$this->display ();
	}
	
	/**
	 * 更新行为
	 * 
	 * @author huajie <banhuajie@163.com>
	 */
	public function saveAction() {
		$res = D ( 'Action' )->update ();
		if (! $res) {
			$this->error ( D ( 'Action' )->getError () );
		} else {
			$this->success ( $res ['id'] ? '更新成功！' : '新增成功！', Cookie ( '__forward__' ) );
		}
	}
	
	/**
	 * 会员状态修改
	 * 
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function changeStatus($method = null) {
		$id = array_unique ( ( array ) I ( 'id', 0 ) );
		if (in_array ( C ( 'USER_ADMINISTRATOR' ), $id )) {
			$this->error ( "不允许对超级管理员执行该操作!" );
		}
		$id = is_array ( $id ) ? implode ( ',', $id ) : $id;
		if (empty ( $id )) {
			$this->error ( '请选择要操作的数据!' );
		}
		$map ['uid'] = array (
				'in',
				$id 
		);
		switch (strtolower ( $method )) {
			case 'forbiduser' :
				$this->forbid ( 'ucenter_member', $map );
				break;
			case 'resumeuser' :
				$this->resume ( 'ucenter_member', $map );
				break;
			case 'deleteuser' :
				$this->delete ( 'ucenter_member', $map );
				break;
			default :
				$this->error ( '参数非法' );
		}
	}
	public function add($username = '', $password = '', $repassword = '', $email = '', $mobile = '') {
		if (IS_POST) {
			/* 检测密码 */
			if ($password != $repassword) {
				$this->error ( '密码和重复密码不一致！' );
			}
			/* 调用注册接口注册用户 */
			$User = new UserApi ();
			$uid = $User->register ( $username, $password, $email, $mobile );
			
			if (0 < $uid) { // 注册成功
				$user = array (
						'uid' => $uid,
						'nickname' => $username,
						'status' => 1 
				);
				if (! M ( 'Member' )->add ( $user )) {
					$this->error ( '用户添加失败！' );
				} else {
					$this->success ( '用户添加成功！', U ( 'index' ) );
				}
			} else { // 注册失败，显示错误信息
				$this->error ( $this->showRegError ( $uid ) );
			}
		} else {
			
			$this->meta_title = '新增用户';
			$this->display ();
		}
	}
	
	/**
	 * 获取用户注册错误信息
	 * 
	 * @param integer $code
	 *        	错误编码
	 * @return string 错误信息
	 */
	private function showRegError($code = 0) {
		switch ($code) {
			case - 1 :
				$error = '用户名长度必须在16个字符以内！';
				break;
			case - 2 :
				$error = '用户名被禁止注册！';
				break;
			case - 3 :
				$error = '用户名被占用！';
				break;
			case - 4 :
				$error = '密码长度必须在6-30个字符之间！';
				break;
			case - 5 :
				$error = '邮箱格式不正确！';
				break;
			case - 6 :
				$error = '邮箱长度必须在1-32个字符之间！';
				break;
			case - 7 :
				$error = '邮箱被禁止注册！';
				break;
			case - 8 :
				$error = '邮箱被占用！';
				break;
			case - 9 :
				$error = '手机格式不正确！';
				break;
			case - 10 :
				$error = '手机被禁止注册！';
				break;
			case - 11 :
				$error = '手机号被占用！';
				break;
			default :
				$error = '未知错误';
		}
		return $error;
	}
	
	/**
	 * 获取属性信息并缓存
	 * 
	 * @param integer $id
	 *        	属性ID
	 * @param string $field
	 *        	要获取的字段名
	 * @return string 属性信息
	 */
	public function get_userfiled($model_id, $group = true) {
		
		/* 非法ID */
		if (empty ( $model_id ) || ! is_numeric ( $model_id )) {
			return '';
		}
		
		/* 获取属性 */
		if (! isset ( $list [$model_id] )) {
			$map = array (
					'model_id' => $model_id 
			);
			$extend = M ( 'Usermodel' )->getFieldById ( $model_id, 'extend' );
			
			if ($extend) {
				$map = array (
						'model_id' => array (
								"in",
								array (
										$model_id,
										$extend 
								) 
						) 
				);
			}
			$info = M ( 'Userfiled' )->where ( $map )->select ();
			$list [$model_id] = $info;
			// S('attribute_list', $list); //更新缓存
		}
		
		$attr = array ();
		foreach ( $list [$model_id] as $value ) {
			$attr [$value ['id']] = $value;
		}
		
		if ($group) {
			$sort = M ( 'Usermodel' )->getFieldById ( $model_id, 'field_sort' );
			
			if (empty ( $sort )) { // 未排序
				$group = array (
						1 => array_merge ( $attr ) 
				);
			} else {
				$group = json_decode ( $sort, true );
				$keys = array_keys ( $group );
				foreach ( $group as &$value ) {
					foreach ( $value as $key => $val ) {
						$value [$key] = $attr [$val];
						unset ( $attr [$val] );
					}
				}
				
				if (! empty ( $attr )) {
					$group [$keys [0]] = array_merge ( $group [$keys [0]], $attr );
				}
			}
			$attr = $group;
		}
		return $attr;
	}
}
