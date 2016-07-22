<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Wechat\Controller;

use User\Api\UserApi;
use Common\Controller\Wechat;
use Admin\Controller\PublicController;
use Common\Api\ExpressApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {
	private $wechat;
	
	/* 用户中心首页 */
	public function index() {
		if (!is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		$this->meta_title = '用户中心';
		// 获取用户详细信息
		$info = M ( 'Member' )->where ( array (
				'uid' => HUID 
		) )->find ();
		$User = new UserApi ();
		$info = array_merge ( $info, $User->infoall ( HUID ) ); // 获取用户所有信息
		
		$info['nickname'] = get_nickname($info['uid']);
		$this->assign ( 'info', $info );
		// 获取用户表字段
		$userfiled = M ( 'Userfiled' )->where ( array (
				'is_show' => 1 
		) )->select ();
		$this->assign ( 'userfiled', $userfiled );
		
		/* 升级还需业绩 */
		$group = get_group_by_uid($info['id']);
		$where['group_id'] = $group['id'];
		$upinfo = M('upgroup')->where($where)->find();
		$zong = $upinfo['shopping_price'] + $upinfo['team_price'];
		$yeji = get_yeji($info['id']) + get_yeji_by_uid($info['id']);
		$this->assign("yeji",$yeji);
		$shengyu = $zong - $yeji;
		$this->assign("shengyu",$shengyu);
		
		
		
		/*检查升级条件*/
		$check = $this->checkup(is_login());
		if($check){
			$this->assign("up","on");
		}else{
			$this->assign("up","off");
		}
		
		
		/* 获取web_access_token */
		//1从数据库获取
		/*$wechatinfo = get_wechatinfo_by_id();
		if(NOW_TIME>=$wechatinfo['web_expires_in']){
			//数据库的access_token已过期
			$nowurl = "http://" . $_SERVER ['HTTP_HOST'] . U('user/index');
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$wechatinfo['appID']."&redirect_uri=" . urlencode ( $nowurl ) . "&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
			header("Location:".$wxurl);
			exit;
		}else{
			$this->assign("web_access_token",$wechatinfo['web_access_token']);
		}*/
		
		
		$this->assign("group",get_group_by_uid(is_login()));
		
		$this->display ();
	}
	
	public function cash(){
		
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		
		if(IS_POST){
			
			$money = I('post.money');
			
			$oldmoney = M('Member')->where("uid=".is_login())->getField("money");
			
			if($money>$oldmoney){
				echo "提现金额大于可提金额！";
				exit;
			}
			
			
			$openid = M('ucenter_member')->where('id='.is_login())->getField("openid");
			
			import ( 'Common.Wxpay.WxPayPubHelper' );
			import ( 'Common.Wxpay.WxPaypubconfig' );
			$pay = new \Commonutilpub();
			
			$Obj['mch_appid'] = \WxPaypubconfig::APPID;
			$Obj['mchid'] = \WxPaypubconfig::MCHID;
			$Obj['nonce_str'] = \WxPaypubconfig::KEY;
			$Obj['partner_trade_no'] = '"'.NOW_TIME.'"';
			$Obj['openid'] = $openid;
			$Obj['check_name'] = "NO_CHECK";
			$Obj['amount'] = $money*100;
			$Obj['desc'] = "提现";
			$Obj['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
			$Obj['sign'] = $pay->getSign($Obj);
			
			$xml = $pay->arrayToXml($Obj);
			$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
			$result = $pay->postXmlSSLCurl($xml,$url);
			$result = $pay->xmlToArray($result);
			
			if($result['result_code']=="SUCCESS"){
				$data['uid'] = is_login();
				$data['money'] = $money;
				$data['add_time'] = NOW_TIME;
				$data['money_type'] = 2;
				M("MoneyLog")->add($data);

				$newmoney = $oldmoney - $money;
				M('Member')->where("uid=".is_login())->setField("money",$newmoney);
				echo "true";
				exit;
			}else{
				echo $result['err_code_des'];
				exit;
			}
		}else{
			$info = M ( 'Member' )->where ( array ('uid' => HUID) )->find ();
			$this->assign("info",$info);
			$this->assign("face",get_face());
			$this->display();
		}
		
	}
	
	
	/**
	 * 升级申请
	 */
	public function shengji(){
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		$uid = is_login();
		
		$to_group_id = $this->checkup($uid);
		
		if($to_group_id){
			$group = get_group_by_uid($uid);
			$data['uid'] = $uid;
			$data['group_id'] = $group['id'];
			$data['to_group_id'] = $to_group_id;
			$data['create_time'] = NOW_TIME;
			$data['status'] = 0;
			$id = M('upapply')->add($data);
			if($id){
				$this->success("您的申请已提交，请等待管理员审核！");
			}
		}
		
		
	}
	
	
	/**
	 * 确认收货
	 */
	public function queren($id){
		
		$where['id'] = $id;
		$status = M('Order')->where($where)->getField("status");
		if($status>=2){
			$this->error("订单已经被确认过了！");
			exit;
		}
		
		$where['id'] = $id;
		$status = M('Order')->where($where)->getField("status");
		if($status<2){
			$data['status'] = 2;
			$data['update_time'] = NOW_TIME;
			// 已确认
			M('Order')->where($where)->save($data);
			/* 自动分销 */
			$info = M('Order')->where($where)->find();
			$this->auto_fx($info['id']);
			
			/* 自动升级 */
			$this->autoup($info['uid']);
				
			/* 自动归属团队 */
			$status = M('auth_group_access')->where('uid='.$info['uid'])->getField("status");
			if($status==0){
				$auth_data['status'] = 1;
				M('auth_group_access')->where('uid='.$info['uid'])->save($auth_data);
			}
			
			/* 积分处理 */
			$this->jifen_action($id);
			
			
			/* 通知用户 */
			$puid = M('auth_group_access')->where("uid=".$info['uid'])->getField("puid");
			$openid = "";
			if($puid && $puid!=0){
				$openid = M('ucenter_member')->where("id=".$puid)->getField("openid");
			}
			if($openid){
				$orderlist = M('Orderlist')->where("order_id=".$info['id'])->select();
				for($i=0;$i<count($orderlist);$i++){
					$str[$i] = $orderlist[$i]['pro_id']."x".$orderlist[$i]['length'];
				}
				$str = implode(",",$str);
				
				$this->wechat = new Wechat (); // 实例化 wechat 类
				$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
				$data ['first'] = "您的会员:".get_nickname($info['uid'])."已确认收货了一笔订单！";
				$data ['keyword1'] = $info['uid'];
				$data ['keyword2'] = $info['id']."，订单金额：".$info['price']."，产品:".$str;
				$data ['keyword3'] = date("Y-m-d H:i:s");
				$data ['Remark'] = "订单ID：".$info['id']."，订单金额：".$info['price']."，产品:".$str;
				$this->wechat->tplmsg ( $openid, "3JxDB6L9-7KlnS-THS5KCWtmZK-CX0HEvvQNTfV8rQQ", $url, $data );
			}
			
			$str = "2015-12-28";
			$time = strtotime($str);
			$map = array();
			$map['reg_time'] = array("gt",$time);
			$ids = M("ucenter_member")->where($map)->getField("id",true);
			if(in_array($info['uid'],$ids)){//新注册用户才享受优分
				/*优分*/
				$data = array();
				$data['uid'] = $info['uid'];
				$data['puid'] = $puid;
				$data['yf_type'] = 1;
				$data['val'] = 1;
				$info = M("youfen")->where($data)->find();
				if(!$info){//没有数据则添加，有数据则不予理会
					$data['create_time'] = NOW_TIME;
					M("youfen")->add($data);
					M("Member")->where("uid=".$puid)->setInc("youfen");//优分+1
				}
			}
			
			$this->success("确认完成！",U("index"));
		}else{
			$this->error("确认订单失败！");
		}
	}
	
	public function jifen_action($orderid){
		
		$info = M("order")->where("id=".$orderid)->find();
		$orderlist = M('Orderlist')->where("order_id=".$orderid)->select();
		$jifen = array();
		for($i=0;$i<count($orderlist);$i++){
			//处理积分
			$where = array();
			$where['id'] = $orderlist[$i]['pro_id'];
			$tjf = M('Product')->where($where)->getField("jifen");
			$jifen[] = $tjf * $orderlist[$i]['length'];
		}
		$jifen = array_sum($jifen);
		$where = array();
		$where['uid'] = $info['uid'];
		M('Member')->where($where)->setInc("jifen",$jifen);
	}
	
	public function jifen_queren($id){
		$where['id'] = $id;
		$data['status'] = 2;
		$data['update_time'] = NOW_TIME;
		$res = M('jifen_order')->where($where)->save($data);
		if($res!=false){
			$this->success("订单确认成功！");
		}else{
			$this->error("订单确认失败！");
		}
	}
	
	
	public function checkmoney(){
		echo "<meta charset='UTF-8'>";
		$member = M('Member')->select();
		for($i=0;$i<count($member);$i++){
			$money = 0;
			$xxmoney = 0;
			$loglist = M('MoneyLog')->where("uid=".$member[$i]['uid'])->select();
			$xfmoney = 0;
			for($j=0;$j<count($loglist);$j++){
				if($loglist[$j]['money_type']==1){
					$money = $money + $loglist[$j]['money'];
				}else if($loglist[$j]['money_type']==0){
					$xxmoney = $xxmoney + $loglist[$j]['money'];
				}else{
					$xfmoney = $xfmoney + $loglist[$j]['money'];
				}
			}
			if($member[$i]['money']!=0 || $member[$i]['xxmoney']!=0)
			echo $member[$i]['nickname']."，UID:".$member[$i]['uid']."线上总金额：".$money."，线下总金额：".$xxmoney."消费金额：".$xfmoney."，现线上：".$member[$i]['money']."，线下：".$member[$i]['xxmoney']."，余值：".($money+$xxmoney-$xfmoney)."<br>";
			
		}
		exit;
		
	}
	
	
	/**
	 * 手动确认订单
	 */
	public function sdqr($id){
		
		$where['id'] = $id;
		$status = M('Order')->where($where)->getField("status");
		if($status>=2){
			//return false;
		}
		
		$where['id'] = $id;
		$status = M('Order')->where($where)->getField("status");
		if($status<=2){
			$data['status'] = 2;
			$data['update_time'] = NOW_TIME;
			/* 已确认 */
			M('Order')->where($where)->save($data);
		
			/* 自动分销 */
		
			$info = M('Order')->where($where)->find();
		
			$result = $this->auto_fx($info['id']);
		    
			// 自动升级
			$this->autoup($info['uid']);
			
			//积分处理
			$this->jifen_action($id);
			
		
			// 自动归属团队 
			$status = M('auth_group_access')->where('uid='.$info['uid'])->getField("status");
			if($status==0){
				$auth_data['status'] = 1;
				M('auth_group_access')->where('uid='.$info['uid'])->save($auth_data);
			}
			// 通知用户
			$puid = M('auth_group_access')->where("uid=".$info['uid'])->getField("puid");
			$openid = "";
			if($puid && $puid!=0){
				$openid = M('ucenter_member')->where("id=".$puid)->getField("openid");
			}
			if($openid){
				$orderlist = M('Orderlist')->where("order_id=".$info['id'])->select();
				for($i=0;$i<count($orderlist);$i++){
					$str[$i] = $orderlist[$i]['pro_id']."x".$orderlist[$i]['length'];
				}
				$str = implode(",",$str);
				$this->wechat = new Wechat (); // 实例化 wechat 类
				$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
				$data ['first'] = "您的会员:".get_nickname($info['uid'])."已确认收货了一笔订单！";
				$data ['keyword1'] = "UID:".$info['uid'];
				$data ['keyword2'] = $info['id']."，订单金额：".$info['price']."，产品:".$str;
				$data ['keyword3'] = date("Y-m-d H:i:s");
				$data ['Remark'] = "订单ID：".$info['id']."，订单金额：".$info['price']."，产品:".$str;
				$this->wechat->tplmsg ( $openid, "3JxDB6L9-7KlnS-THS5KCWtmZK-CX0HEvvQNTfV8rQQ", $url, $data );
			}
			
			/*优分*/
			
			$str = "2015-12-28";
			$time = strtotime($str);
			$map = array();
			$map['reg_time'] = array("gt",$time);
			$ids = M("ucenter_member")->where($map)->getField("id",true);
			if(in_array($info['uid'],$ids)){//新注册用户才享受优分
				$data = array();
				$data['uid'] = $info['uid'];
				$data['puid'] = $puid;
				$data['yf_type'] = 1;
				$data['val'] = 1;
				$info = M("youfen")->where($data)->find();
				if(!$info){//没有数据则添加，有数据则不予理会
					$data['create_time'] = NOW_TIME;
					M("youfen")->add($data);
					M("Member")->where("uid=".$puid)->setInc("youfen");//优分+1
				}
			}
			
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 系统自动程序
	 */
	public function clr_order($pass){
		if($pass!="cheewoadmin") $this->error("未授权访问！",U('Index/index'));
		
		//自动确认收货
		$this->auto_queren();
		
		//自动评价
		$this->auto_pingjia();
		
		//自动关闭订单
		$this->auto_close_order();
		
		echo "1";
		exit;
	}
	
	/**
	 * 3天关闭订单
	 * @return boolean
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_close_order(){
		$time10 = strtotime("-3 days",NOW_TIME);
		$time10 = time_format($time10,"Y-m-d");
		$time10 = strtotime($time10);
		$where['create_time'] = array("lt",$time10);
		$where['status'] = 0;
		$ids = M('Order')->where($where)->setField("status",-1);
		return true;
	}
	
	
	/**
	 * 12天自动评价
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_pingjia(){
		
		$time10 = strtotime("-12 days",NOW_TIME);
		$time10 = time_format($time10,"Y-m-d");
		$time10 = strtotime($time10);
		$where['create_time'] = array("lt",$time10);
		$where['status'] = 2;
		$where['expressnum'] = array("gt",0);
		$where['express_time'] = array("neq",0);
		$ids = M('Order')->where($where)->getField("id",true);
		
		for($i=0;$i<count($ids);$i++){
			
			$orderlist = M('Orderlist')->where("order_id=".$ids[$i])->select();
			for($j=0;$j<count($orderlist);$j++){
				$data = array();
				$data['pro_id'] = $orderlist[$j]['pro_id'];
				$data['uid'] = $orderlist[$j]['uid'];
				$data['xing'] = 5;
				$data['content'] = "好评！";
				$data['create_time'] = NOW_TIME;
				$data['status'] = 1;
				M('prints')->add($data);
				M('Order')->where("id=".$ids[$i])->setField("status",3);
			}
		}
		
	}
	
	public function auto_queren(){
		$time10 = strtotime("-9 days",NOW_TIME);
		$time10 = time_format($time10,"Y-m-d");
		$time10 = strtotime($time10);
		$where['create_time'] = array("lt",$time10);
		$where['status'] = 1;
		$where['expressnum'] = array("gt",0);
		$where['express_time'] = array("neq",0);
		$ids = M('Order')->where($where)->getField("id");
		$this->sdqr($ids);
		/*
		for($i=0;$i<count($ids);$i++){
			$this->sdqr($ids[$i]);
		}*/
		return true;
	}
	
	/**
	 * 自动升级
	 * @param unknown $uid
	 * @return boolean
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function autoup($uid){
		//临时方式
		//$uids = M('ucenter_member')->getField("id",true);
		$uids = array();
		$uids = get_team_desc($uid);
		$uids[] = $uid;
		foreach ($uids as $uid){//循环升级
			$where['autoup'] = 1;
			$group = get_group_by_uid($uid);
			$group_id = $group['id'];
			if($group_id){
				/*查询升级条件*/
				$where['group_id'] = $group_id;
				$upinfo = M('upgroup')->where($where)->find();
				if($upinfo){
					/*找到有升级条件匹配*/
					$to_group_id = $upinfo['to_group_id'];
					$nowdata = $this->now_info($uid);//当前用户的条件
					/*去掉多余数据*/
					unset($upinfo['id']);
					unset($upinfo['group_id']);
					unset($upinfo['to_group_id']);
					unset($upinfo['autoup']);
					unset($upinfo['status']);
					/* 此时的剩下的条件，跟当前用户的条件就是对等的，接下来进行条件对比 */
					$status = true;
					foreach ($upinfo as $key => $value){
						if($nowdata[$key]<$upinfo[$key]){
							/* 前当值小于目标值，很遗憾，升级失败 */
							$status = false;
						}
					}
					if($status){
						/* 咦，全部满足。赶快升级吧！ */
						$data['group_id'] = $to_group_id;
						$res = M('AuthGroupAccess')->where('uid='.$uid)->save($data);
						if($res!=false){//升级成功,接下来追平返点处理


							//   2016-04-10 版追平返点制度
							$shangji = M('auth_group_access')->where("uid=".$uid)->getField("puid");
							$sjgroup = get_group_by_uid($shangji);
							$sjgroupid = $sjgroup['id'];//原上级分组
							$auth = M('auth_group_access')->where("uid=".$uid)->find();

							/* 从未有过追平 且 升级后的当前级别 等于 上级级别 */
							$zp = false;
							$cy = false;
							if($auth['zp']==0 && $auth['group_id']==$sjgroupid){
								/* 如果升级满足追平条件 */
								if($sjgroupid==$to_group_id){
									M('auth_group_access')->where("uid=".$uid)->setField("zp",1);//追平上级
									$zp = true;
								}
							}else if($auth['zp']==1 && $auth['group_id']>=$sjgroupid){//脱离情况1：已经是追平 且 升级后的当前级别 >= 上级
								/* 如果升级满足脱离条件：已追平上级再次升级 或者 本身就与上级平级 */
								$p = M('auth_group_access')->where("uid=".$auth['puid'])->getField("puid");
								M('auth_group_access')->where("uid=".$uid)->setField("puid",$p);//超越
								//记得要把追平状态还原哦，没设置之前等于还把超越后的上级追平了，囧
								M('auth_group_access')->where("uid=".$uid)->setField("zp",0);//设置追平状态
								$cy = true;
							}else if($auth['zp']==0 && $auth['group_id']==9 && $sjgroupid==1){//脱离情况2：未追平，原用户升级至黄金，但上级用户仍然是会员时脱离
								$p = M('auth_group_access')->where("uid=".$auth['puid'])->getField("puid");
								M('auth_group_access')->where("uid=".$uid)->setField("puid",$p);//超越
								//记得要把追平状态还原哦，没设置之前等于还把超越后的上级追平了，囧
								M('auth_group_access')->where("uid=".$uid)->setField("zp",0);//设置追平状态
								$cy = true;
							}

							$map = array();
							$map['puid'] = $uid;
							$map['zp'] = 1;
							M("auth_group_access")->where($map)->setField("zp",0);//脱离之前追平关系，回归正常收益

							$where = array();
							$where['uid'] = $uid;
							$info = M("auth_group_access")->where($where)->find();

							$this->wechat = new Wechat (); // 实例化 wechat 类
							$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
							$data = array();
							$data ['first'] = "升级通知";
							$data ['keyword1'] = "CheeWoPHP";
							$str = "";
							if($cy){
								$str = "升级并超越";
							}
							if($zp){
								$str = "升级并追平";
							}
							if($str==""){
								$str = "普通升级";
							}
							$data ['keyword2'] = "UID:".$uid."：".$str;
							$str = get_group_title_by_id($info['group_id']);
							$str .= "，当前上级：".get_nickname($info['puid'])."，级别：".get_group_title_by_uid($info['puid']);
							$data ['remark'] = "升级至：".$str;
							$uids = array(791,194,160);
							foreach($uids as $val){
								$openid = get_openid($val);
								$this->wechat->tplmsg ( $openid, "WhjkbxMHR4On_1K5bxkt2HigoTlUTKUgfdp9TVRJydE", $url, $data );
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * 当前用户所满足的条件
	 * @param unknown $uid
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function now_info($uid){
	
		/* 累计消费金额 */
		$data['shopping_price'] = get_yeji_by_uid($uid) ? get_yeji_by_uid($uid) : 0;
		/* 团队业绩 */
		$data['team_price'] = get_yeji($uid) + $data['shopping_price'];
	
		/* 团队总人数  */
		$team = get_shengji_team($uid);//直属团队
		/* 普通会员数 */
		$data['shopping_num'] = get_team_filter_to_num($team,1) ? get_team_filter_to_num($team,1) : 0;
		
	
		/* 三级代理数 */
		$data['sanji_num'] = get_team_filter_to_num($team, 9) ? get_team_filter_to_num($team, 9) : 0;
		/* 二级代理数 */
		$data['erji_num'] = get_team_filter_to_num($team, 10) ? get_team_filter_to_num($team, 10) : 0;
		/* 一级代理数  */
		$data['yiji_num'] = get_team_filter_to_num($team, 11) ? get_team_filter_to_num($team, 11) : 0;
		/*保证金*/
		$data['bzj'] = get_bzj($uid);
		return $data;
	
	}
	
	public function sanjipid($uid){
		$puid = M('auth_group_access')->where("uid=".$uid)->getField("puid");
		$puid = M('auth_group_access')->where("uid=".$puid)->getField("puid");
		$puid = M('auth_group_access')->where("uid=".$puid)->getField("puid");
		if($puid){
			$sanji = M('auth_group_access')->where("uid=".$puid)->getField("group_id");
			if($sanji>1){
				return true;
			}else{
				return false;
			}
		}else{//级数没有超过三级的，也认定通过
			return true;
		}
	}
	
	/**
	 * 自动分销
	 * @param unknown $order_id
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_fx($order_id) {
		/* 三级归属 */
		$order_info = array();
		$order_info = M('order')->where('id='.$order_id)->find();

		/* 后添加规则 当前用户没有追平或超越 同时 上级还是个会员 时就不进行分佣 */
		$puid = array();
		$puid = M('auth_group_access')->where("uid=".$order_info['uid'])->find();
		$pgroup = M('auth_group_access')->where("uid=".$puid['puid'])->getField("group_id");
		
		
		/* 12月31号新增规则 */
		$sj = C("SANJIFENYONG");
		
		if($sj){//开启三级会员分佣制度
			$sanji = $this->sanjipid($order_info['uid']);
			if(!$sanji){//超过三级，不分佣
				return false;
			}
		}else{//关闭三级会员分佣制度，上级是会员的时候就不分拥了
			if($pgroup==1 && $puid['zp']==0){
				return true;
			}
		}
		$aauth = array();
		$aauth = $this->auth_tree ( $order_info['uid'] );
		$auth = explode ( ",", $aauth );
		$where = array();
		$where ['order_id'] = $order_id;
		$list = array();
		$list = M ( 'orderlist' )->where ( $where )->select ();
		/* 循环归属 */
		for($i = 0; $i < count ( $list ); $i ++) {
			if($list[$i]){
				if(!$this->check_kill($list[$i]['pro_id'],$order_info['create_time'])){
					if($this->auto_fenpei($list[$i], $auth,$order_info['uid'])){
						//echo $list[$i]['id'];
					}
				}else{
					//该产品为秒杀购买,不参与分佣
				}
			}
		}
		
	}

	public function check_kill($proid,$create_time)
	{
		$where = array();
		$where['start_time'] = array("lt",$create_time);
		$where['end_time'] = array("gt",$create_time);
		$killid = M("seckill")->where($where)->getField("id");
		if($killid){
			$where = array();
			$where['pid'] = $killid;
			$where['proid'] = $proid;
			$killid = M("seckill_list")->where($where)->getField("id");
			if($killid){
				return true;
				exit;
			}
		}
		return false;
	}
	
	/**
	 * 自动分配
	 * @param unknown $orderlist
	 * @param unknown $auth
	 * @param unknown $uid
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_fenpei($orderlist, $auth,$uid) {
		
		
		$price = 0;
		
		/* 产品信息 */
		$pro_info = array();
		$pro_info = D('Product')->detail($orderlist['pro_id']);
		
		/* 购买订单者的分组 */
		$buyuidgroup = array();
		$buyuidgroup = get_group_by_uid ( $uid );
		$buygroupid = $buyuidgroup['id'];
		
		/* 购买者的上级 */
		$info = array();
		$info = M('auth_group_access')->where("uid=".$uid)->find();
		$sjgroup =get_group_by_uid($info['puid']);
		$sjgroupid = $sjgroup['id'];
		
		$sjsta = false;
		
		
		//追平返点
		if($info['zp']==1){
			$sjsta = true;
			$sjid = M('auth_group_access')->where("uid=".$info['puid'])->getField("puid");//当前上级的上级
			$pgroupid = M('auth_group_access')->where("uid=".$sjid)->getField("group_id");//上级的上级的分组
			$noprice = $info['puid'];
		}
		
		/* 获取各级别分佣金额 */
		$mmap['proid'] = $orderlist['pro_id'];
		$mmap['status'] = 1;
		$pids = M('MarketingList')->where($mmap)->getField("pid",true);
		
		if($pids){
			$mwhere['id'] = array("in",implode(",",$pids));
			$mw['id'] = $orderlist['order_id'];
			$mwtime = M('Order')->where($mw)->getField("create_time");
			$mwhere['start_time'] = array("lt",$mwtime);
			$mwhere['end_time'] = array("gt",$mwtime);
			
			$mid = M('marketing')->where($mwhere)->getField("id");
			if($mid){
				$mmap1['pid'] = $mid;
				$mmap1['proid'] = $orderlist['pro_id'];
				$pro_info = M('MarketingList')->where($mmap1)->find();
			}
		}
		$allprice = $this->back_auto_fyprice($pro_info,$orderlist['length']);

		
		$price = 0;
		/**
		 * 循环购买体系
		 * 从购买者开始循环
		 */
		$index = $buygroupid;
		if($index==1){
			$index = 8;
		}
		
		/* 手动修改一下总经理的关系 */
		if($index==13){
			$index = 12;
		}
		for($i=$index+1;$i<=13;$i++){
			$data = array();
			/* 滚动分佣池 */
			$price = $price + $allprice[$i];
			
			$nprice = $allprice[$i];
			
			$temp = false;
			
			if($buygroupid==12){
				$tempprice = $allprice[12];
				$temp = true;
			}
			if($buygroupid==13){
				$tempprice =  $allprice[12] + $allprice[13];
				$temp = true;
			}
			
			if($temp==true){
				$data ['uid'] = $uid; // 用户
				$data['puid'] = $uid;
				$data['order_id'] = $orderlist['order_id'];
				$data['pro_id'] = $orderlist['pro_id'];
				$data['length'] = $orderlist['length'];
				$data['add_time'] = NOW_TIME;
				$data['money'] = $tempprice;
				$data['money_type'] = 0;
				M('MoneyLog')->add($data);
				$where['uid'] = $uid;
				$money = 0;
				$templog = array();
				$templog['uid'] = $uid;
				$templog['nowmoney'] = M('Member')->where($where)->getField("xxmoney");
				if($data['money']){
					M('Member')->where($where)->setInc("xxmoney",$data['money']);
					$data['nowmoney'] = $templog['nowmoney'];
					$this->tzadmin($data);
				}else{
					$data['nowmoney'] = $templog['nowmoney'];
					$this->tzadmin($data);
				}
			}
			//echo "<br>当前级别：".$i.",池金额：".$price;
			foreach ( $auth as $value ) {
				
				if(!$value) continue;

				//追平则不参与
				if($sjsta){
					if($value==$auth) continue;
				}
				
				$nowprice = $price;
				
				/* 当前分佣者的分组 */
				$nowgroupid = M('auth_group_access')->where("uid=".$value)->getField("group_id");
				/* 找到要分佣的 */
				if($i==$nowgroupid){
					$data ['uid'] = $value; //用户
					$data['puid'] = $uid;
					$data['order_id'] = $orderlist['order_id'];
					$data['pro_id'] = $orderlist['pro_id'];
					$data['length'] = $orderlist['length'];
					$data['add_time'] = NOW_TIME;
					if($i==12 || $i==13){
						$tp = $price - $nprice;
						if($tp==0){
							$data['money'] = $nprice;
							$data['money_type'] = 0;
							$price = 0;
							$data['money'] = round($data['money'],2);
							if($data['money']>0){
								/*添加记录*/
								if(M('MoneyLog')->add($data)){
									/*修改余额*/
									$where = array();
									$where['uid'] = $value;
									$money = 0;
									$templog = array();
									$templog['uid'] = $uid;
									$templog['nowmoney'] = M('Member')->where($where)->getField("xxmoney");
									if($data['money']){
										$where = array();
										$where['uid'] = $value;
										$res = M('Member')->where($where)->setInc("xxmoney",$data['money']);
										$data['nowmoney'] = $templog['nowmoney'];
										$this->tzadmin($data);
									}else{
										$data['nowmoney'] = $templog['nowmoney'];
										$this->tzadmin($data);
									}
									$templog['aftermoney'] = M('Member')->where($where)->getField("xxmoney");
									$templog['orderid'] = $data['order_id'];
									$templog['add_time'] = NOW_TIME;
									M('TempLog')->add($templog);
									if($templog['aftermoney']<$templog['nowmoney']){
										$this->tzadmin2($templog);
									}
								}
							}
						}else if($tp>0){
							if($i==12){
								$data['money'] = $price - $nprice;
							}else if($i==13){
								$data['money'] = $price - $allprice[12] - $allprice[13];
							}
							
							$data['money_type'] = 1;
							$data['money'] = round($data['money'],2);
							if($data['money']>0){
								/*添加记录*/
								if(M('MoneyLog')->add($data)){
									$price = 0;
									/*修改余额*/
									$where['uid'] = $value;
									$money = 0;
									$templog = array();
									$templog['uid'] = $uid;
									$templog['nowmoney'] = M('Member')->where($where)->getField("money");
									if($data['money']){
										$where = array();
										$where['uid'] = $value;
										$res = M('Member')->where($where)->setInc("money",$data['money']);
										$data['nowmoney'] = $templog['nowmoney'];
										$this->tzadmin($data);
									}else{
										$data['nowmoney'] = $templog['nowmoney'];
										$this->tzadmin($data);
									}
									$templog['aftermoney'] = M('Member')->where($where)->getField("money");
									$templog['orderid'] = $data['order_id'];
									$templog['add_time'] = NOW_TIME;
									M('TempLog')->add($templog);
									if($templog['aftermoney']<$templog['nowmoney']){
										$this->tzadmin2($templog);
									}
								}
							}
							/*分线下*/
							if($i==12){
								$data['money'] = $nprice;
							}else if($i==13){
								$data['money'] = $allprice[12] + $allprice[13];
							}
							$data['money_type'] = 0;
							$data['money'] = round($data['money'],2);
							if($data['money']>0){
								/*添加记录*/
								M('MoneyLog')->add($data);
								/*修改余额*/
								$where['uid'] = $value;
								$templog = array();
								$templog['uid'] = $uid;
								$templog['nowmoney'] = M('Member')->where($where)->getField("xxmoney");
								if($data['money']){
									$where = array();
									$where['uid'] = $value;
									$res = M('Member')->where($where)->setInc("xxmoney",$data['money']);
									$data['nowmoney'] = $templog['nowmoney'];
									$this->tzadmin($data);
								}else{
									$data['nowmoney'] = $templog['nowmoney'];
									$this->tzadmin($data);
								}
								$templog['aftermoney'] = M('Member')->where($where)->getField("xxmoney");
								$templog['orderid'] = $data['order_id'];
								$templog['add_time'] = NOW_TIME;
								M('TempLog')->add($templog);
								if($templog['aftermoney']<$templog['nowmoney']){
									$this->tzadmin2($templog);
								}
							}
						}
					}else{
						//找到之后拿走当前分佣池
						$data['money'] = $price;
						$data['money'] = round($data['money'],2);
						if($data['money']>0){
							$price = 0;
							/*添加记录*/
							if(M('MoneyLog')->add($data)){
								/*修改余额*/
								$money = 0;
								$templog = array();
								$templog['uid'] = $uid;
								$templog['nowmoney'] = M('Member')->where($where)->getField("money");
								if($data['money']){
									$where = array();
									$where['uid'] = $value;
									$res = M('Member')->where($where)->setInc("money",$data['money']);
									$data['nowmoney'] = $templog['nowmoney'];
									$this->tzadmin($data);
								}else{
									$data['nowmoney'] = $templog['nowmoney'];
									$this->tzadmin($data);
								}
								$templog['aftermoney'] = M('Member')->where($where)->getField("money");
								$templog['orderid'] = $data['order_id'];
								$templog['add_time'] = NOW_TIME;
								M('TempLog')->add($templog);
								if($templog['aftermoney']<$templog['nowmoney']){
									$this->tzadmin2($templog);
								}
							}
							
						}
					}
				}
				
				/* 追平返点 */
				if($sjsta){
					if($i==$nowgroupid){
						/* 当前级别就是购买者上级的上级了，购买者追平上级 */
						if($buygroupid==$sjgroupid && $nowgroupid==$pgroupid){
							$bili = $this->get_zp($pgroupid);
							$fandian = $nowprice * $bili;
							/*修改被返点人余额*/
							$bfddata = array();
							$bfddata['uid'] = $info['puid'];
							$bfddata['order_id'] = $orderlist['order_id'];
							$bfddata['pro_id'] = $orderlist['pro_id'];
							$bfddata['length'] = $orderlist['length'];
							$bfddata['puid'] = $value;
							$bfddata['add_time'] = NOW_TIME;
							$bfddata['money'] = $fandian;
							M('MoneyLog')->add($bfddata);
							$where1 = array();
							$where1['uid'] = $bfddata['uid'];
							$money = 0;
							if($bfddata['money']){
								M('Member')->where($where1)->setInc("money",$bfddata['money']);
								$this->tzadmin($bfddata);
							}
							/* 修改返点人余额 */
							$fddata = array();
							$fddata['uid'] = $value;
							$fddata['puid'] = $info['puid'];
							$fddata['order_id'] = $orderlist['order_id'];
							$fddata['pro_id'] = $orderlist['pro_id'];
							$fddata['length'] = $orderlist['length'];
							$fddata['add_time'] = NOW_TIME;
							$fddata['money'] = $fandian;
							$fddata['money_type'] = 6;
							$nowprice = 0;
							M('MoneyLog')->add($fddata);
							$where2 = array();
							$where2['uid'] = $fddata['uid'];
							$money = 0;
							if($fddata['money']){
								M('Member')->where($where2)->setDec("money",$fddata['money']);
								$this->tzadmin($fddata);
							}
						}
					}
				}
			}
		}
		return true;
	}
	
	
	public function tzadmin($data){
		if($data['money_type']==0){
			$ordertype = "线下";
		}else if($data['money_type']==1){
			$ordertype = "线上";
		}else{
			$ordertype = "其他";
		}
		$this->wechat = new Wechat (); // 实例化 wechat 类
		$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
		$content ['first'] = "订单号：".$data['order_id']."已确认收货，购买用户：".$data['puid']."，分佣用户：".$data['uid'];
		$content ['keyword1'] = "CheeWoPHP";
		$content ['keyword2'] = $ordertype."分佣：".$data['money'];
		$where = array();
		$where['uid'] = $data['uid'];
		$xxmoney = M('Member')->where($where)->getField("xxmoney");
		$xsmoney = M('Member')->where($where)->getField("money");
		
		$list = M('money_log')->where($where)->select();
		$xs = 0;
		$xx = 0;
		$xf = 0;
		for($i=0;$i<count($list);$i++){
			if($list[$i]['money_type']==1){
				$xs  = $xs + $list[$i]['money'];
			}else if($list[$i]['money_type']==0){
				$xx = $xx + $list[$i]['money'];
			}else{
				$xf = $xf + $list[$i]['money'];
			}
		}
		$str = "之前".$ordertype."金额：".$data['nowmoney']."，分之后线上金额：".$xsmoney."，线下金额：".$xxmoney."，合计：".($xsmoney+$xxmoney)."；";
		$str .= "分佣合计线上：".$xs."，线下：".$xx."，消费：".$xf."，合计：".($xs+$xx-$xf);
		$content ['remark'] = $str; 
		$url = "http://uxiango.2k6k.com";
		//$this->wechat->tplmsg ( "omLoBuK6wrYLbbGfE2MoEj8HwgLU", "2G7_sk5HG06uJ9KDNS7da38P20rQWHC9PjCSrJcLU2A", $url, $content );
		
	}
	
	public function tzadmin2($data){
		$this->wechat = new Wechat (); // 实例化 wechat 类
		$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
		$data ['first'] = "订单号：".$data['orderid']."，用户：".$data['uid']."，之前金额：".$data['nowmoney']."，之后：".$data['aftermoney'];
		$data ['keyword1'] = $data['uid'];
		$data ['keyword2'] = "少了：".$data['nowmoney']-$data['aftermoney'];
		$data ['Remark'] = "详情到分佣体系查看！";
		$url = "";
		//$this->wechat->tplmsg ( "omLoBuK6wrYLbbGfE2MoEj8HwgLU", "WhjkbxMHR4On_1K5bxkt2HigoTlUTKUgfdp9TVRJydE ", $url, $data );
	}
	
	public function get_zp($groupid){
		switch ($groupid){
			case 10:
				return 0.3;
				break;
			case 11:
				return 0.3;
				break;
			case 12:
				return 0.2;
				break;
			case 13:
				return 0.2;
				break;
		}
		
	}
	
	public function back_auto_fyprice($pro_info,$length){
		$data[9] = ($pro_info['sanji_price']*$length) * ($pro_info ['sanji_lirunbi']/100);//三级返点
		$data[10] = ($pro_info['erji_price']*$length) * ($pro_info ['erji_lirunbi']/100);//二级返点
		$data[11] = ($pro_info['yiji_price']*$length) * ($pro_info ['yiji_lirunbi']/100);//一级返点
		$data[12] = ($pro_info['yxzj_price']*$length) * ($pro_info ['yxzj_lirunbi']/100);//营销总监
		$data[13] = ($pro_info['fgs_price']*$length) * ($pro_info ['fgs_lirunbi']/100);//分公司总经理
		return $data;
	}
	
	/**
	 * 自动递归三级归属
	 *
	 * @param unknown $uid
	 * @param number $z_index
	 * @return Ambigous <string, unknown>
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auth_tree($uid, $z_index = 0) {
		global $str;
		$where ['uid'] = $uid;
		$puid = M ( 'auth_group_access' )->where ( $where )->getField ( "puid" );
		if ($puid != 0 ) {
			//echo $z_index;
			if ($z_index == 4) {
				$str .= $puid;
			} else {
				$str .= $puid . ",";
			}
			$z_index = $z_index + 1;
			$this->auth_tree ( $puid, $z_index );
		}
		return $str;
	}
	
	
	
	/**
	 * 检查升级条件
	 * @param unknown $uid
	 * @return boolean
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function checkup($uid){
		$group = get_group_by_uid($uid);
		$group_id = $group['id'];
		if($group_id){
			/*查询升级条件*/
			$where['group_id'] = $group_id;
			$upinfo = M('upgroup')->where($where)->find();
			if($upinfo){
				/*找到有升级条件匹配*/
				$to_group_id = $upinfo['to_group_id'];
				$nowdata = $this->now_info($uid);//当前用户的条件
				/*去掉多余数据*/
				unset($upinfo['id']);
				unset($upinfo['group_id']);
				unset($upinfo['to_group_id']);
				unset($upinfo['autoup']);
				unset($upinfo['status']);
				/* 此时的剩下的条件，跟当前用户的条件就是对等的，接下来进行条件对比 */
				foreach ($upinfo as $key => $value){
					if($nowdata[$key]<$upinfo[$key]){
						/* 前当值小于目标值，很遗憾，升级失败 */
						return false;
					}
				}
				/* 咦，全部满足。赶快升级吧！ */
				return $to_group_id;
			}else{
				/* 未找到升级条件 */
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	
	/**
	 * 微信注册页面
	 * @param unknown $mobile
	 * @param unknown $verify
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function register($mobile='', $verify='') {
		if (! C ( 'USER_ALLOW_REGISTER' )) {
			$this->error ( '注册已关闭' );
			exit;
		}
		if (IS_POST) { // 注册用户
			/*if($verify!=session("rand")){
				$this->error("验证码不正确！");
				exit;
			}*/
			$userinfo = session("userinfo");
			$userinfo = json_decode($userinfo,true);
			$username = $userinfo['nickname'];
			$nickname = $username;
			$password = '123456';
			$email = NOW_TIME."@cheewo.com";
			
			/* 调用注册接口注册用户 */
			$User = new UserApi ();
			if(!$username){
				$username = $mobile;
			}

			
			if(!$username || $username==""){
				$username = $mobile;
			}
			$uid = $User->register ( $username, $password, $email,$mobile );
			if (0 < $uid) { // 注册成功
				
				/** 自动归属 **/
				$fromopenid = session("fromopenid");
				$fromid = 0;
				if(!empty($fromopenid)){
					$where['openid'] = $fromopenid;
					$fromid = M('ucenter_member')->where($where)->getField("id");
					if(!$fromid) $fromid = 0;
				}
				$auth_data['uid'] = $uid;
				$auth_data['puid'] = $fromid;
				$auth_data['group_id'] = 1;//新用户注册为普通用户
				M('auth_group_access')->add($auth_data);
				
				/* 更新微信扩展信息到主表 */
				$user_data['id'] = $uid;
				$user_data['openid'] = $userinfo['openid'];
				$user_data['headimgurl'] = $userinfo['headimgurl'];
				M('ucenter_member')->save($user_data);
				/* 更新扩展资料到副表 */
				$data ['uid'] = $uid;
				$data ['status'] = 1;
				$data ['nickname'] = $username;
				if (! M ( 'Member' )->add ( $data )) $this->error ( '写入记录出错' );
				/* 模板消息通知上级 */
				if($fromopenid){
					$this->wechat = new Wechat (); // 实例化 wechat 类
					$tpldata['first'] = "恭喜你邀请好友注册成功！";
					$tpldata['keyword1'] = "UID:".$uid.",昵称:".$userinfo['nickname'];
					$tpldata['keyword2'] = date("Y-m-d H:i:s");
					$tpldata['remark'] = "快去我的团队看看吧！";
					$tpldataurl = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
					$tplid = "FI0sTP1eEkkInXVx76iiaZzX6QTmRcm9TcYE68dK4xI";
					$this->wechat->tplmsg($fromopenid, $tplid, $tpldataurl, $tpldata);
				}
				
				/* 注册成功时自动登录 */
				$Member = D ( 'Member' );
				$Member->login($uid);
				$this->redirect("User/index");
			} else { // 注册失败，显示错误信息
				if($uid==="nophone"){
					$this->error ( "手机号已被注册，请换个号码重试。或联系客服人员找回密码！" );
				}else{
					$this->error ( "注册失败！请与客服人员联系！" );
				}
				
			}
		} else { // 显示注册表单
			$this->meta_title = "免费注册";
			$this->display ();
		}
	}
	
	
	
	/**
	 * 微信注册页面
	 * @param unknown $mobile
	 * @param unknown $verify
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function regapi($mobile='', $verify='') {
		if (! C ( 'USER_ALLOW_REGISTER' )) {
			echo ( '注册已关闭' );
			exit;
		}
		if (IS_POST) { // 注册用户
			/*if($verify!=session("rand")){
			 $this->error("验证码不正确！");
			exit;
			}*/
			$userinfo = session("userinfo");
			$userinfo = json_decode($userinfo,true);
			$username = $userinfo['nickname'];
			$password = '123456';
			$email = NOW_TIME."@cheewo.com";
				
			/* 调用注册接口注册用户 */
			$User = new UserApi ();
			if(!$username){
				$username = $mobile;
			}
			$username = preg_replace('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', '', $userinfo['nickname']);
			$username = trim($username);
			if(!$username || $username==""){
				$username = $mobile;
			}
			$uid = $User->register ( $username, $password, $email,$mobile );
			if (0 < $uid) { // 注册成功
	
				/** 自动归属 **/
				$fromopenid = session("fromopenid");
				$fromid = 0;
				if(!empty($fromopenid)){
					$where['openid'] = $fromopenid;
					$fromid = M('ucenter_member')->where($where)->getField("id");
					if(!$fromid) $fromid = 0;
				}
				$auth_data['uid'] = $uid;
				$auth_data['puid'] = $fromid;
				$auth_data['group_id'] = 1;//新用户注册为普通用户
				M('auth_group_access')->add($auth_data);
	
				/* 更新微信扩展信息到主表 */
				$user_data['id'] = $uid;
				$user_data['openid'] = $userinfo['openid'];
				$user_data['headimgurl'] = $userinfo['headimgurl'];
				M('ucenter_member')->save($user_data);
				/* 更新扩展资料到副表 */
				$data ['uid'] = $uid;
				$data ['status'] = 1;
				$data ['nickname'] = $username;
				if (! M ( 'Member' )->add ( $data )){
					echo ( '写入记录出错' );
					exit;
				}
	
				/* 模板消息通知上级 */
				if($fromopenid){
					$this->wechat = new Wechat (); // 实例化 wechat 类
					$tpldata['first'] = "恭喜你邀请好友注册成功！";
					$tpldata['keyword1'] = $userinfo['nickname'];
					$tpldata['keyword2'] = date("Y-m-d H:i:s");
					$tpldata['remark'] = "快去我的团队看看吧！";
					$tpldataurl = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
					$tplid = "FI0sTP1eEkkInXVx76iiaZzX6QTmRcm9TcYE68dK4xI";
					$this->wechat->tplmsg($fromopenid, $tplid, $tpldataurl, $tpldata);
				}
				/* 注册成功时自动登录 */
				$Member = D ( 'Member' );
				$Member->login($uid);
				echo "1";
				exit;
			} else { // 注册失败，显示错误信息
				if($uid==="nophone"){
					echo "手机号已被注册，请换个号码重试。或联系客服人员找回密码！";
				}else{
					echo "注册失败！请与客服人员联系！";
				}
				exit;
			}
		}
	}
	
	/* 登录页面 */
	public function login($username = '', $password = '', $verify = '') {
		if (C ( "WECHAT_LOGIN_TYPE" ) == 2) { // 手动登录
			if (IS_POST) { // 登录验证
				/* 后台开启验证码验证时才验证，否则不验证 */
				if (C ( 'USER_LOGIN_VERIFY' ) == 1) {
					/* 检测验证码 */
					if (! check_verify ( $verify )) {
						$this->error ( '验证码输入错误！' );
					}
				}
				
				/* 调用UC登录接口登录 */
				$user = new UserApi ();
				$uid = $user->login ( $username, $password );
				if (0 < $uid) { // UC登录成功
					/* 登录用户 */
					$Member = D ( 'Member' );
					if ($Member->login ( $uid )) { // 登录用户
					                               // TODO:跳转到登录前页面
						$this->success ( '登录成功！', U ( 'User/index' ) );
					} else {
						$this->error ( $Member->getError () );
					}
				} else { // 登录失败
					switch ($uid) {
						case - 1 :
							$error = '用户不存在或被禁用！';
							break; // 系统级别禁用
						case - 2 :
							$error = '密码错误！';
							break;
						default :
							$error = '未知错误！';
							break; // 0-接口参数错误（调试阶段使用）
					}
					$this->error ( $error );
				}
			} else { // 显示登录表单
				$this->meta_title = "用户登录";
				$this->display ();
			}
		} else {
			$wechatinfo = get_wechatinfo_by_id();
			echo "<meta content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;\" name=\"viewport\" />";
			$nowurl = "http://" . $_SERVER ['HTTP_HOST'] . U ( 'User/WechatLogin', array (
					'wechatid' => $wechatinfo['wechatid']
			) );
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$wechatinfo['appID']."&redirect_uri=" . urlencode ( $nowurl ) . "&response_type=code&scope=".C('WECHAT_LOGIN_AUTH')."&state=STATE#wechat_redirect";
			//echo "<script>window.location.href='".$wxurl."';</script>";
			$this->success ( "正在为你跳转登陆！", $wxurl );
			exit ();
		}
	}
	
	public function reg_send_sms($mobile){
		$rand = rand(1000,9999);
		session("rand",$rand);
		$info = send_sms($mobile,$rand);
		echo true;
		exit;
	}
	
	
	/**
	 * 我的优惠券
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function coupons(){
		if (! is_login ()) {
			$this->redirect("User/login");
			exit;
		}
		$where['uid'] = is_login();
		$where['status'] = 1;
		$list = M('coupons_list')->where($where)->select();
		for($i=0;$i<count($list);$i++){
			$gz = M('coupons')->where("id=".$list[$i]['pid'])->find();
			if(NOW_TIME>$gz['end_time']){
				unset($list[$i]);
				continue;
			}else{
				$list[$i] = array_merge($gz,$list[$i]);
			}
		}
		$this->assign("list",$list);
		$userhblist = M('userhb_log')->where($where)->select();
		for($i=0;$i<count($userhblist);$i++){
			
			$info = M('userhb')->where("id=".$userhblist[$i]['pid'])->find();
			$userhblist[$i]['content'] = $info['content'];
			$userhblist[$i]['hb_type'] = $info['hb_type'];
			
		}
		$this->assign("userhblist",$userhblist);
		
		$this->display();
		
	}
	
	/**
	 * 我的订单
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function myorder($type=10) {
		if (! is_login ()) {
			$this->redirect("User/login");
			exit;
		}
		
		$map ['uid'] = is_login ();
		if($type!=10){
			if($type!=4){
				$map['status'] = $type;
			}else{
				$map['status'] = array("gt",0);
			}
		}
		$list = M ( 'Order' )->where ( $map )->order ( 'id desc' )->select (); // 30天前订单
		for($i = 0; $i < count ( $list ); $i ++) {
			$list [$i] ['Statustxt'] = $this->order_status ( $list [$i] ['Status'] );
			$list [$i] ['lists'] = M ( 'Orderlist' )->where ( 'order_id=' . $list [$i] ['id'] )->select ();
			
			if($list[$i]['express_time']!=0){
				$time = strtotime("+10 days",$list[$i]['express_time']);
				$cha = time_cha($time, NOW_TIME);
				$list[$i]['sy_d'] = $cha['d'];
				$list[$i]['sy_h'] = $cha['h'];
			}
		}
		$this->assign ( "list", $list );
		$this->assign ( "info", array (
				'title' => '我的订单' 
		) );
		
		$this->assign("type",$type);
		
		$this->display ();
	}
	
	
	
	/**
	 * 积分订单
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function jforder($type=10) {
		if (! is_login ()) {
			$this->redirect("User/login");
			exit;
		}
	
		$map ['uid'] = is_login ();
		if($type!=10){
			if($type!=4){
				$map['status'] = $type;
			}else{
				$map['status'] = array("gt",0);
			}
		}
		$list = M ( 'jifen_order' )->where ( $map )->order ( 'id desc' )->select (); // 30天前订单
		for($i = 0; $i < count ( $list ); $i ++) {
			$list [$i] ['Statustxt'] = $this->order_status ( $list [$i] ['Status'] );
			$list [$i] ['lists'] = M ( 'jifen_orderlist' )->where ( 'order_id=' . $list [$i] ['id'] )->select ();
				
			if($list[$i]['express_time']!=0){
				$time = strtotime("+10 days",$list[$i]['express_time']);
				$cha = time_cha($time, NOW_TIME);
				$list[$i]['sy_d'] = $cha['d'];
				$list[$i]['sy_h'] = $cha['h'];
			}
		}
		$this->assign ( "list", $list );
		$this->assign ( "info", array (
				'title' => '积分订单'
		) );
	
		$this->assign("type",$type);
	
		$this->display ();
	}
	
	/**
	 * 批量删除订单
	 */
	public function changeStatus($method="deleteorder"){
		$id = array_unique((array)I('id',0));
		$id = is_array($id) ? implode(',',$id) : $id;
		if ( empty($id)) {
			$this->error('请选择要操作的数据!');
		}
		$map['id'] =   array('in',$id);
		switch ( strtolower($method) ){
			case 'deleteorder':
				$res = M('Order')->where($map)->setField("status",-1);
				if($res){
					$this->success("取消成功！");
					exit;
				}else{
					$this->error("取消失败！");
					exit;
				}
				break;
			default:
				$this->error('参数非法');
		}
	}
	
	/**
	 * 退换货
	 * 0退货，1换货
	 */
	public function tuihuo($order_id,$type=0){
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		if(IS_POST){
			$data = I('post.');
			if(isset($data['thtype']) && $data['thtype']=="dp"){
				if(!isset($data['ids']) || count($data['ids'])==0){
					$this->error("请选择要退款的产品！");
					exit;
				}
			}
			
			$where['uid'] = is_login();
			$where['order_id'] = $data['order_id'];
			$where['status'] = 1;
			$info = M('tuihuo')->where($where)->find();
			if($info){
				$this->success("您已提交过申请，请等待工作人员与您联系！");
				exit;
			}
			$data['uid'] = is_login();
			$data['create_time'] = NOW_TIME;
			$id = M('tuihuo')->add($data);
			if($id){
				if(isset($data['thtype']) && $data['thtype']=="dp"){
					for($i=0;$i<count($data['ids']);$i++){
						$listdata['pid'] = $id;
						$listdata['listid'] = $data['ids'][$i];
						$listdata['length'] = $data['num'][$i];
						M('tuihuo_list')->add($listdata);
					}
				}
				
				$this->wechat = new Wechat (); // 实例化 wechat 类
				$url = "http://".C('WEB_SITE_DOMAIN')."/index/index.html";
				$data = array();
				$data ['first'] = "有新的退货啦！";
				$data ['keyword1'] = "系统";
				$data ['keyword2'] = "会员UID：".is_login()."发起了新的退货！订单ID：".$where['order_id'];
				$data ['remark'] = "详情请到后台查看！";
				$thadmin = C('THADMIN');
				$thadmin = explode(",",$thadmin);
				foreach ($thadmin as $val){
					$openid = M('ucenter_member')->where("id=".$val)->getField("openid");
					$result = $this->wechat->tplmsg ( $openid, "2G7_sk5HG06uJ9KDNS7da38P20rQWHC9PjCSrJcLU2A", $url, $data );
				}
				
				
				$this->success("您的申请已提交，稍后将会有工作人员与您联系！");
				exit;
			}else{
				$this->error("申请提交失败，请重试！");
				exit;
			}
		}else{
			
			$where['id'] = $order_id;
			$list = M ( 'Order' )->where ( $where )->order ( 'id desc' )->select ();
			for($i = 0; $i < count ( $list ); $i ++) {
				$list [$i] ['Statustxt'] = $this->order_status ( $list [$i] ['Status'] );
				$list [$i] ['lists'] = M ( 'Orderlist' )->where ( 'order_id=' . $list [$i] ['id'] )->select ();
				if($list[$i]['express_time']!=0){
					$time = strtotime("+10 days",$list[$i]['express_time']);
					$cha = time_cha($time, NOW_TIME);
					$list[$i]['sy_d'] = $cha['d'];
					$list[$i]['sy_h'] = $cha['h'];
				}
			}
			
			$this->assign("order_id",$order_id);
			
			$this->assign ( "list", $list );
			
			$this->assign("type",$type);
			$this->display();
			
		}
		
	}
	
	
	public function check_paypass(){
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		
		$paypass = M('ucenter_member')->where('id='.is_login())->getField("paypass");
		if(!$paypass){
			echo "false";
		}else{
			echo "true";
		}
		exit;
	}
	
	public function team($uid=0){

		
		if($uid==0){
			$gp = get_group_by_uid(is_login());
		}else{
			$gp = get_group_by_uid($uid);
		}

		$puid = M("auth_group_access")->where("uid=".$uid)->getField("puid");
		$this->assign("puid",$puid);
		
		$gp = $gp['id'];
		
		
		$list = get_team_one($uid);//获取团队数据
		
		$theArray = array();
		
		$flist = get_team_filter($list,1);
		$temp = array();
		$temp['title'] = M("auth_group")->where("id=1")->getField("title");
		$temp['count'] = count($flist);
		$temp['list'] = $flist;
		$theArray[] = $temp;
		
		if($gp>=9){
			$flist = get_team_filter($list,9);
			$temp = array();
			$temp['title'] = M("auth_group")->where("id=9")->getField("title");
			$temp['count'] = count($flist);
			$temp['list'] = $flist;
			$theArray[] = $temp;
		}
		
		
		if($gp>=10){
			$flist = get_team_filter($list,10);
			$temp = array();
			$temp['title'] = M("auth_group")->where("id=10")->getField("title");
			$temp['count'] = count($flist);
			$temp['list'] = $flist;
			$theArray[] = $temp;
		}
		
		if($gp>=11){
			$flist = get_team_filter($list,11);
			$temp = array();
			$temp['title'] = M("auth_group")->where("id=11")->getField("title");
			$temp['count'] = count($flist);
			$temp['list'] = $flist;
			$theArray[] = $temp;
		}
		
		if($gp>=12){
			$flist = get_team_filter($list,12);
			$temp = array();
			$temp['title'] = M("auth_group")->where("id=12")->getField("title");
			$temp['count'] = count($flist);
			$temp['list'] = $flist;
			$theArray[] = $temp;
		}
		
		
		if($uid==0){
			$this->assign("title","我的直属团队（".count(get_team_one(is_login()))."）");
		}else{
			$this->assign("title",get_nickname($uid)."的直属团队（".count(get_team_one($uid))."）");
		}
		$this->assign("uid",$uid);
		
		$this->assign("list",$theArray);
		$this->display();
	}
	
	public function setpaypass(){
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		
		
		if(IS_POST){
			
			$password = I('post.password');
			$paypass = M('ucenter_member')->where('id='.is_login())->getField("paypass");
			if(!$paypass){
				$status = M('ucenter_member')->where('id='.is_login())->setField("paypass",think_encrypt($password));
				if($status){
					echo "true";
					exit;
				}else{
					echo "error";
				}
			}else{
				echo "have";
			}
			exit;
		}
		$this->assign("face",get_face());
		$this->display();

	}
	
	public function update_paypass(){
		if (! is_login ()) {
			Cookie ( '__furl__',"/".CONTROLLER_NAME."/".ACTION_NAME);
			$this->redirect("User/login");
			exit;
		}
		$paypass = M('ucenter_member')->where('id='.is_login())->getField("paypass");
		if(!$paypass){
			$this->redirect("setpaypass");
			exit;
		}
		
		if(IS_POST){
			
			$oldpass = I('post.oldpass');
			$newpass = I('post.newpass');
			
			if(think_encrypt($oldpass)===$paypass){
				
				$res = M('ucenter_member')->where('id='.is_login())->setField("paypass",think_encrypt($newpass));
				if($res){
					echo "true";
				}else{
					echo "false";
				}
			}else{
				echo "noold";
			}
			exit;
		}
		
		$this->assign("face",get_face());
		$this->display();
	}
	
	
	/**
	 * 我的余额
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function yue(){
		if (! is_login ()) {
			$this->redirect("User/login");
			exit;
		}
		$where['uid'] = is_login();
		$group = get_group_by_uid(is_login());
		/*if($group['id']==12 || $group['id']==13){
			$where['money_type'] = array("in","1,2,3,5,6");
		}*/
		$where['money_type'] = array("in","1,2,3,5,6,7");
		$list = M('MoneyLog')->where($where)->order('add_time desc')->select();
		$this->assign("list",$list);
		
		$money = M('Member')->where($where)->getField("money");
		$this->assign("money",$money);
		
		$this->assign("group",get_group_by_uid(is_login()));
		
		$this->display();
	}
	
	public function youfen(){
		if (! is_login ()) {
			$this->redirect("User/login");
			exit;
		}
		$where['puid'] = is_login();
		$list = M('youfen')->where($where)->order('create_time desc')->select();
		$this->assign("list",$list);
		$youfen = M('Member')->where("uid=".is_login())->getField("youfen");
		$this->assign("youfen",$youfen);
		$this->display();
	}
	
	Public function qiandao() {
		$this->assign ( "info", array (
				'title' => '每日签到' 
		) );
		$this->display ();
	}
	private function order_status($status) {
		$status_array = array (
				'待付款',
				'待处理,请耐心等待',
				'支付成功,等待发货',
				'已发货,待签收',
				'已完成' 
		);
		return $status_array [$status];
	}
	public function jifen() {
		$info = D ( 'Member' )->find ( is_login () );
		$info ['title'] = "我的积分";
		$this->assign ( "info", $info );
		$this->display ();
	}
	public function view() {
		$map ['uid'] = is_login ();
		$viewlist = M ( 'view' )->where ( $map )->select ();
		for($i = 0; $i < count ( $viewlist ); $i ++) {
			$temp = D ( 'Product' )->detail ( $viewlist [$i] ['proid'] );
			$list [$i] = $temp;
			$list [$i] ['info'] = $viewlist [$i];
		}
		$this->assign ( "list", $list );
		$this->assign ( "info", array (
				'title' => '浏览记录' 
		) );
		$this->display ();
	}
	public function pingjia() {
		$map ['uid'] = is_login ();
		$map ['Status'] = 3; // 已完成订单
		$allorder = M ( 'Order' )->where ( $map )->getfield ( "id", true );
		$allorder = implode ( ",", $allorder );
		$ordermap ['order_id'] = array (
				'in',
				$allorder 
		);
		$oderlist = M ( 'Orderlist' )->where ( $ordermap )->select ();
		for($i = 0; $i < count ( $oderlist ); $i ++) {
			$pro = D ( 'Product' )->detail ( $oderlist [$i] ['pro_id'] );
			$list [$i] = $pro;
			$list [$i] ['order'] = $oderlist [$i];
		}
		$this->assign ( "list", $list );
		$this->assign ( "info", array (
				'title' => '产品评价' 
		) );
		$this->display ();
	}
	
	public function track($id){
		$where['id'] = $id;
		$info = M('order')->where($where)->find();
		if(empty($info['express_com']) || empty($info['expressnum'])){
			$this->assign("error",1);
		}else{
			$e = new ExpressApi ();
			$result = $e->getorder ( $info ['express_com'], $info ['expressnum'] , "baidu" );
			$type = "baidu";
			if($result['status']==0 && $result['msg']=="ok"){
				
			}else{
				$result['error'] = 1;
			}
			$this->assign("etype",$type);
			$this->assign ( "expinfo", $info );
			$this->assign ( "result", $result );
		}
		$this->assign ( "info", array (
				'title' => '订单跟踪'
		) );
		$this->display();
	}
	
	
	public function addpj($id) {
		if (IS_POST) {
			$map = I ( 'post.' );
			$map ['uid'] = is_login ();
			$map ['create_time'] = NOW_TIME;
			$res = M ( 'prints' )->add ( $map );
			if ($res) {
				$data['pj'] = 1;
				$where['id'] = $map['order_id'];
				M('Orderlist')->where($where)->save($data);
				/*查询订单还有没有待评价的商品*/
				$order_map['order_id'] = $id;
				$order_map['pj'] = 0;
				$result = M('Orderlist')->where($order_map)->count();
				if($result==0){
					/* 全部已评价 */
					$order_data['status'] = 3;
					M('Order')->where('id='.$id)->save($order_data);
				}
			}
		} else {
			$where['order_id'] = $id;
			$orderlist = M('Orderlist')->where($where)->select();
			$this->assign("orderlist",$orderlist);
			$this->assign ( "info", array (
					'title' => '发表评价' 
			) );
			$this->assign("id",$id);
			$this->display ();
		}
	}
	public function Address() {
		$map ['uid'] = is_login ();
		$list = M ( 'Address' )->where ( $map )->select ();
		$this->assign ( "list", $list );
		
		$sheng = M('Linkage')->where("pid=3133")->select();
		$this->assign("sheng",$sheng);
		
		$this->display ();
	}
	
	public function deladdress($id){
		
		$res = M('Address')->where("id=".$id)->delete();
		if($res){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
		
	}
	
	public function like() {
		$likelist = M ( 'like' )->where ( 'uid=' . is_login () )->select ();
		for($i = 0; $i < count ( $likelist ); $i ++) {
			$list [] = D ( 'Product' )->detail ( $likelist [$i] ['proid'] );
		}
		$this->assign ( "list", $list );
		$this->assign ( "info", array (
				'title' => '我的收藏' 
		) );
		$this->display ();
	}
	
	/* 退出登录 */
	public function logout() {
		if (is_login ()) {
			D ( 'Member' )->logout ();
			$this->success ( '退出成功！', U ( 'Index/index' ) );
		} else {
			$this->redirect ( 'User/login' );
		}
	}
	
	/**
	 * 从其他页面跳转去登录
	 * @param unknown $fromurl
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function jump_login_from_other($fromurl){
		Cookie ( '__furl__',$fromurl);
		$this->redirect("User/login");
		exit;
		
	}
	
	/**
	 * 绑定微信
	 *
	 * @param unknown $code        	
	 * @param unknown $state        	
	 */
	public function WechatLogin($code, $state) {
		if (IS_POST) {
			$openid = $_POST ['openid'];
			$username = $_POST ['username'];
			$password = $_POST ['password'];
			$user = new UserApi ();
			$uid = $user->login ( $username, $password );
			if (0 < $uid) { // UC登录成功
				$data ['open_id'] = $openid;
				$status = $user->update ( $uid, $data );
				if ($status ['status']) {
					D ( 'Member' )->login ( $uid );
					$status = 1;
					$this->success ( '绑定成功', U ( 'User/index' ) );
				} else {
					$this->error ( $status ['info'] );
				}
			}
		} else {
			$this->wechat = new Wechat (); // 实例化 wechat 类
			/* 获取openid */
			$selfopenid = session("selfopenid");
			$userinfo = $this->userinfo;
			$fromopenid = session("fromopenid");
			/* 调用UC登录接口登录 */
			$user = new UserApi ();
			$status = $user->wechat_login ( $selfopenid );
			if ($status == - 1) { // 未绑定微信
				$where['openid'] = $fromopenid;
				$frominfo = M('ucenter_member')->where($where)->find();
				$this->assign("frominfo",$frominfo);
				$this->assign("userinfo",$this->userinfo);
				$this->display("register");
			} else if($status==0) {//账户被禁用！
				$this->error("您的账户已被禁用，请与客服人员联系！",U('Index/index'));
			}else{
				$map['openid'] = $userinfo['openid'];
				M('ucenter_member')->where($map)->setField("headimgurl",$userinfo['headimgurl']);
				$nickname = $userinfo['nickname'];
				M('Member')->where("uid=".$status)->setField("nickname",$nickname);
				M('ucenter_member')->where($map)->setField("username",$nickname);
				D ( 'Member' )->login ( $status ); // 添加登录信息
				$url = Cookie ( '__furl__' );
				$this->success("登录成功，正在为您跳转登录前的页面！",$url);
				exit;
			}
		}
	}
	
	public function test(){
		$userinfo = session("userinfo");
		
		$userinfo = json_decode($userinfo,true);
		
		dump($userinfo);
		
		$tmpStr = json_encode($userinfo['nickname']);
		$tmpStr = preg_replace("#(\\\ue[0-9a-f]{3})#ie", "",$tmpStr);
		dump($tmpStr);
		$tmpStr = preg_replace("#(\\\ue[0-9a-f]{4})#ie", "",$tmpStr);
		dump($tmpStr);
		$text = json_decode($tmpStr);
		dump($text);
		
	}
	
	
	public function userhb(){
		$where['uid'] = is_login();
		$where['status'] = 1;
		$list = M('userhb')->where($where)->order("create_time desc")->select();
		for($i=0;$i<count($list);$i++){
			$list[$i]['last'] = M('userhb_log')->where("pid=".$list[$i]['id'])->count();
			$list[$i]['last'] = $list[$i]['times'] - $list[$i]['last'];
		}
		$this->assign("list",$list);
		$this->assign ( "info", array ('title' => '红包管理') );
		$this->display();
	}
	
	public function myhb(){
		$where['uid'] = is_login();
		$where['status'] = 1;
		$list = M('userhb')->where($where)->order("create_time desc")->select();
		for($i=0;$i<count($list);$i++){
			$list[$i]['last'] = M('userhb_log')->where("pid=".$list[$i]['id'])->count();
			$list[$i]['last'] = $list[$i]['times'] - $list[$i]['last'];
		}
		$this->assign("list",$list);
		$this->assign ( "info", array ('title' => '发出去的红包') );
		$this->display();
	}
	
	
	public function user_addhb(){
		$this->assign ( "info", array ('title' => '发红包') );
		/* paypass */
		$paypass = M ( 'ucenter_member' )->where ( "id=" . is_login () )->getField ( "paypass" );
		if ($paypass) {
			$this->assign ( "paypass", "is" );
		} else {
			$this->assign ( "paypass", "nis" );
		}
		
		//money
		$money = M('Member')->where("uid=".is_login())->getField("money");
		$this->assign("money",$money);
		$this->display();
	}
	
	public function pay_hb(){
		if(IS_POST){
			$data = I('post.');
			
			if($data['hb_type']==2){
				$data['countmoney'] = $data['money'];
			}else if($data['hb_type']==1){
				$data['countmoney'] = $data['money'] * $data['times'];
			}
			$data['uid'] = is_login();
			$data['create_time'] = NOW_TIME;
			$id = M('userhb')->add($data);
			if($id){
				//money
				$money = M('Member')->where("uid=".is_login())->getField("money");
				$this->assign("yue",$money);
				$this->assign ( "face", get_face () );
				$info['id'] = $id;
				$info['price'] = $data['countmoney'];
				$this->assign("info",$info);
				$this->display();
			}else{
				$this->error("发红包失败！",U('userhb'));
			}
		}
	}
	
	public function ajax_pay_hb($hbid){
		$where ['id'] = $hbid;
		$info = M ( "userhb" )->where ( $where )->find ();
		$password = I ( 'post.password' );
		$password = think_encrypt ( $password );
		$userinfo = M ( 'ucenter_member' )->where ( "id=" . is_login () )->find ();
		if ($userinfo ['paypass'] === $password) {
			$money = M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->getField ( "money" );
			if ($money >= $info ['countmoney']) {
				$status = M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->setDec ( "money", $info ['countmoney'] );
				if ($status) {
					$res = M ( 'userhb' )->where ( $where )->setField ( "status", 1 );
					if ($res) {
						
						$data['uid'] = is_login();
						$data['money'] = $info['countmoney'];
						$data['order_id'] = 0;
						$data['pro_id'] = 0;
						$data['length'] = 0;
						$data['puid'] = 0;
						$data['add_time'] = NOW_TIME;
						$data['money_type'] = 7;//发红包
						M('MoneyLog')->add($data);
						
						//通知
						$this->wechat = new Wechat (); // 实例化 wechat 类
						$content ['first'] = "您已成功创建一个红包！";
						$content ['keyword1'] = "优享GO";
						$keyword2 = "";
						if($info['hb_type']==1){
							$keyword2 .= "红包类型：普通红包，单个金额：".$info['money'];
						}else{
							$keyword2 .= "红包类型：拼手气红包，总金额：".$info['money'];
						}
						$keyword2 .= "，个数：".$info['times'];
						$content ['keyword2'] = $keyword2;
						$content ['remark'] = "快去发给小伙伴吧！";
						$url = "http://uxiango.2k6k.com/index/userhb/id/".$info['id'].".html";
						$openid = M ( 'ucenter_member' )->where ( 'id=' . $userinfo ['id'] )->getField ( "openid" );
						$this->wechat->tplmsg ( $openid, "2G7_sk5HG06uJ9KDNS7da38P20rQWHC9PjCSrJcLU2A", $url, $content );
						
						echo "true";
						exit ();
					} else {
						/* 修改订单状态失败，退回金额 */
						M ( 'Member' )->where ( 'uid=' . $userinfo ['id'] )->setInc ( "money", $info ['countmoney'] );
						echo "error";
					}
				} else {
					echo "error";
				}
			} else {
				/* 余额不足 */
				echo "nomoney";
			}
		} else {
			/* 支付密码不正确 */
			echo "notpassword";
		}
		exit ();
	}
	
	
	/* 验证码，用于登录和注册 */
	public function verify($id = 1) {
		$verify = new \Think\Verify ();
		$verify->entry ( 1 );
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
	 * 修改密码提交
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function profile() {
		if (! is_login ()) {
			$this->error ( '您还没有登陆', U ( 'User/login' ) );
		}
		if (IS_POST) {
			// 获取参数
			$uid = is_login ();
			$password = I ( 'post.old' );
			$repassword = I ( 'post.repassword' );
			$data ['password'] = I ( 'post.password' );
			empty ( $password ) && $this->error ( '请输入原密码' );
			empty ( $data ['password'] ) && $this->error ( '请输入新密码' );
			empty ( $repassword ) && $this->error ( '请输入确认密码' );
			
			if ($data ['password'] !== $repassword) {
				$this->error ( '您输入的新密码与确认密码不一致' );
			}
			
			$Api = new UserApi ();
			$res = $Api->updateInfo ( $uid, $password, $data );
			if ($res ['status']) {
				$this->success ( '修改密码成功！' );
			} else {
				$this->error ( $res ['info'] );
			}
		} else {
			$this->display ();
		}
	}
	public function get_ticket() {
		$scene = "88" . date ( "s" ) . rand ( 10000, 99999 );
		$appid = "wx44df58ebfcd5ea71";
		$secret = "960abbe366efd323c7d98f1bbb222290";
		$this->wechat = new Wechat ();
		$data = "{\"expire_seconds\": 1800, \"action_name\": \"QR_SCENE\", \"action_info\": {\"scene\": {\"scene_id\": $scene}}}";
		$ticket = $this->wechat->post ( "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN", $data );
		$ticket = $ticket ['ticket'];
		if ($ticket != "") {
			$theArray ['scene'] = $scene;
			$theArray ['create_time'] = NOW_TIME;
			M ( 'WechatTempLog' )->add ( $theArray );
		}
		
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . UrlEncode ( $ticket );
		session ( 'scene', $scene );
		echo $url;
	}
	
	/**
	 * 前端检测调用扫一扫状态
	 */
	public function ticketlogin() {
		$map ['scene'] = session ( 'scene' );
		$result = M ( 'WechatTempLog' )->where ( $map )->find ();
		if ($result ['status'] == 1) {
			/* 调用UC登录接口登录 */
			$user = new UserApi ();
			$status = $user->wechat_login ( $result ['openid'] );
			if ($status == - 1) {
				echo - 1; // "您还未绑定该微信账号，请绑定。";
			} else {
				D ( 'Member' )->login ( $status );
				M ( 'WechatTempLog' )->where ( array (
						'openid' => $result ['openid'] 
				) )->delete ();
				echo 1; // 登录成功！
			}
		} else {
			echo 0;
		}
	}
	public function smsverify($mobile) {
		$content = "验证码：" . rand ( 1000, 9999 ) . "【智网天下】";
		echo send_sms ( $mobile, $content );
	}
	
	/**
	 * 邮箱验证
	 *
	 * @param string $un        	
	 */
	public function emailverify($un = '') {
		if (empty ( $un )) {
			$this->meta_title = "邮箱验证";
			$this->display ();
			exit ();
		}
		if ($un == session ( 'encrypyid' )) {
			$uid = think_decrypt ( $un );
			$user = M ( 'UcenterMember' )->where ( 'id=' . $uid )->find ();
			if ($user ['status'] == 1) {
				$this->error ( '该邮箱已经被验证过了', U ( 'User/login' ) );
			} else {
				$theArray ['status'] = 1;
				M ( 'UcenterMember' )->where ( 'id=' . $uid )->save ( $theArray );
				M ( 'Member' )->where ( 'id=' . $uid )->save ( $theArray );
				$this->success ( '验证成功！', U ( 'User/login' ) );
			}
		} else {
			$this->error ( '参数错误！', U ( 'Index/index' ) );
		}
	}
	public function map() {
		if (isset ( $_GET ['city'] )) {
			$map = I ( 'get.' );
			$list = M ( 'map' )->where ( $map )->select ();
			$this->assign ( "maplist", $list );
			$map ['title'] = "售后地图";
			$this->assign ( "info", $map );
		} else {
			$this->assign ( "info", array (
					'title' => '售后地图' 
			) );
		}
		$city = M ( 'Linkage' )->where ( 'pid=3083' )->select ();
		$pinpai = M ( 'Linkage' )->where ( 'pid=3005' )->select ();
		$this->assign ( "city", $city );
		$this->assign ( "pinpai", $pinpai );
		$this->display ();
	}
	public function detailmap($id) {
		$map = M ( 'map' )->where ( 'id=' . $id )->find ();
		$map ['xy'] = str_ireplace ( ",", "|", $map ['xy'] );
		$this->assign ( "map", $map );
		$this->display ();
	}
}
