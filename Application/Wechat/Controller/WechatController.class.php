<?php

namespace Wechat\Controller;

use Think\Controller;
use Common\Controller\Wechat;
use User\Api\UserApi;

/**
 * 微信控制器
 */
class WechatController extends HomeController {
	private $wechat;
	private $gain;
	/**
	 * 校验 token 、实例化 wechat 类、获取 post数据、写入日志
	 */
	public function __construct() {
		$this->wechat = new Wechat (); // 实例化 wechat 类
		$this->gain = $this->wechat->_receive; // 存入 接收到的数据
		$this->gain ['wechatid'] = $this->gain ['ToUserName'];
		$this->gain ['openid'] = $this->gain ['FromUserName'];
		$this->gain ['logtime'] = NOW_TIME;
		$this->wechat->valid () && M ( "wechat_logs" )->add ( array (
				'content' => json_encode ( $this->gain ),
				'addtime' => NOW_TIME 
		) ); // 插入日志
	}
	/**
	 * 接收所有信息
	 */
	public function index() {
		// 添加、更新关注用户资料
		$user = M ( 'wechat_user' ); // 实例化 WechatUser 对象
		$user_id = $user->field ( 'id' )->where ( "wechatid='" . $this->gain ['wechatid'] . "' and openid='" . $this->gain ['openid'] . "'" )->find ();
		if (0 < $user_id ['id']) { // 存在该用户
			$data = array_merge ( $this->gain, $this->wechat->userinfo ( $this->gain ['openid'] ) ); // 将用户信息合并
			$user->where ( "id='" . $user_id ['id'] . "'" )->save ( $data ); // 更新
		} else {
			$this->gain ['addtime'] = NOW_TIME;
			$data = array_merge ( $this->gain, $this->wechat->userinfo ( $this->gain ['openid'] ) ); // 将用户信息合并
			$user->add ( $data ); // 添加
		}
		
		// 根据不同的消息类型，进行不同的操作
		$mtype = $this->wechat->getRevType (); // 获取消息类型
		$tb_reply = M ( 'wechat_reply as r' ); // 实例化 wechat_reply
		
		$User = new UserApi (); // 调用会员接口
		
		switch ($mtype) {
			// 事件消息
			case Wechat::MSGTYPE_EVENT :
				$event = $this->wechat->getRev ()->getRevEvent (); // 获取事件类型
				if ($event ['event'] == 'subscribe') { // 关注事件
				                                  // 关注后加入会员系统
					$info = $User->info_form_wechat ( 0, false, $this->gain ['openid'] ); // 检测用户是否存在
					if ($info == - 1) { // 不存在进行注册
						$reply ['content'] = $this->auto_back ( 'subscribe' );
						$reply ['type'] = "text";
					} else { // 用户重新关注，开通用户权限；
						$nickname = M ( 'Member' )->getFieldByUid ( $info [0], "nickname" );
						$reply ['content'] = $this->auto_back ( 'subscribeback' );
						$reply ['type'] = "text";
					}
				}
				if ($event ['event'] == 'CLICK') { // 点击事件
					if ($event ['key'] == "qr") {
						qrcode($this->gain['openid']);
						$url = "http://shop.uxiango.cn/Index/qrcode/openid/" . $this->gain['openid'];
						$reply ['type'] = "text";
						$reply ['content'] = "<a href='".$url."'>点击此处下载二维码</a>";
					}else if($event ['key']){
						$where = array();
						$where['keyword'] = array("like","%".$event['key']."%");
						$content = M("wechat_reply")->where($where)->getField("text");
						$reply ['type'] = "text";
						$reply ['content'] = $content;
					}else {
						$reply ['type'] = "text";
						$reply ['content'] = "精彩内容，敬请期待！！！";
					}
				}
				break;
			// 文本消息
			case Wechat::MSGTYPE_TEXT :
				$msg = $this->gain ['Content']; // 获取消息内容
				$map ['type'] = "text";
				$map ['key'] = array (
						"like",
						"%" . $msg . "%" 
				);
				$map ['status'] = 1;
				$info = M ( 'WechatReply' )->where ( $map )->find ();
				if ($info) { // 匹配关键词成功
					$reply = $this->autoreturn ( $info );
				} else {
					
					if($msg=="人工"){
						$reply['content'] = "您好，请问有什么可以帮您的？";
						$reply['type'] = "transfer_customer_service";
					}else{
						$map ['type'] = "auto";
						unset ( $map ['key'] );
						$info = M ( 'WechatReply' )->where ( $map )->find ();
						$reply ['content'] = $info ['text'];
						$reply ['type'] = return_reply_type ( $info ['material'] );
					}
					
				}
				break;
			default :
				$map = array();
				$map ['type'] = "auto";
				$info = M ( 'WechatReply' )->where ( $map )->find ();
				$reply ['content'] = $info ['text'];
				$reply ['type'] = return_reply_type ( $info ['material'] );
				break;
		}
		$reply && $this->wechat->reply ( $reply ['content'], $reply ['type'] ); // 返回推送
	}
	
	/**
	 * 根据类型自动判断回传信息
	 * 
	 * @param unknown $type        	
	 * @return unknown
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_back($type) {
		$where ['type'] = $type;
		$info = M ( 'WechatReply' )->where ( $where )->find ();
		return $info ['text'];
	}
	
	/**
	 * 微信登录自动注册自动注册
	 *
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function auto_reg() {
		$reply ['content'] = "";
		$username = NOW_TIME;
		$password = rand ( 100000, 999999 );
		$mail = $username . "@admin.com";
		$phone = rand ( 13800138000, 18900139000 );
		$uid = $User->register ( $username, $password, $mail, $phone );
		if ($uid) {
			$userdata ['openid'] = $this->gain ['openid'];
			$wechat_user_info = $this->wechat->userinfo ( $this->gain ['openid'] );
			$headimgurl = $wechat_user_info ['headimgurl'];
			$userdata ['headimgurl'] = $headimgurl; // 头像
			$User->update ( $uid, $userdata ); // 将Openid和用户头像更新到会员主表
			$Memberinfo ['uid'] = $uid;
			$Memberinfo ['nickname'] = $wechat_user_info ['nickname']; // 昵称
			$Memberinfo ['sex'] = $wechat_user_info ['sex']; // 性别
			$Memberinfo ['city'] = $wechat_user_info ['city']; // 所在城市
			$Memberinfo ['country'] = $wechat_user_info ['country']; // 所在地区
			$Memberinfo ['province'] = $wechat_user_info ['province'];
			$Memberinfo ['face'] = $headimgurl; // 副表头像
			M ( 'Member' )->add ( $Memberinfo ); // 将用户详细资料添加到会员表
			return $uid;
		} else {
			return false;
		}
	}
	
	// 根据消息判断并自动回复
	public function autoreturn($info = array()) {
		switch ($info ['reply_type']) {
			case 0 : // 回复文本内容
				$reply ['content'] = $info ['content'];
				$reply ['type'] = return_reply_type ( $info ['reply_type'] );
				break;
			case 1 : // 回复素材内容
				$scmap ['wechatid'] = $this->gain ['wechatid'];
				$scmap ['fileid'] = $info ['sc_id'];
				$Media = M ( 'WechatFileMediaId' )->where ( $scmap )->find ();
				// 暂时不作任何有效期判断,3天后再做功能
				$reply ['content'] = $Media ['media_id'];
				$reply ['type'] = "image";
				break;
			case 2 : // 回复素材内容
				$scmap ['wechatid'] = $this->gain ['wechatid'];
				$scmap ['fileid'] = $info ['sc_id'];
				$Media = M ( 'WechatFileMediaId' )->where ( $scmap )->find ();
				// 暂时不作任何有效期判断,3天后再做功能
				$reply ['content'] = $Media ['media_id'];
				$reply ['type'] = "image";
				break;
			case 3 : // 回复素材内容
				$scmap ['wechatid'] = $this->gain ['wechatid'];
				$scmap ['fileid'] = $info ['sc_id'];
				$Media = M ( 'WechatFileMediaId' )->where ( $scmap )->find ();
				// 暂时不作任何有效期判断,3天后再做功能
				$reply ['content'] = $Media ['media_id'];
				$reply ['type'] = "image";
				break;
			case 4 : // 人工接入
				$RgGroup = M ( 'WechatDkhGroup' )->where ( 'id=' . $info ['content'] )->getField ( "id" );
				$RgMap ['group'] = $RgGroup;
				$Dkh = M ( 'WechatDkh' )->where ( $RgMap )->select ();
				$onlinekflist = $this->wechat->get_online_kf_list ();
				if (count ( $onlinekflist ['kf_online_list'] ) == 0) { // 当前客服都不在线
					$reply ['content'] = "当前没有任何客服在线！";
					$reply ['type'] = "text";
				} else {
					$kf_online_list = $onlinekflist ['kf_online_list'];
					$index = 0;
					for($i = 0; $i < count ( $kf_online_list ); $i ++) {
						if ($kf_online_list [$i] ['auto_accept'] > $kf_online_list [$i] ['accepted_case']) { // 有接待能力的客服
							$kfmap ['kf_account'] = $kf_online_list [$i] ['kf_account'];
							$groupid = M ( 'WechatDkh' )->where ( $kfmap )->getField ( "group" );
							if ($groupid == $info ['content']) { // 判断是否是当前分组的成员
								$index = $index + 1; // 累加
								$reply ['content'] = $kf_online_list [$i] ['kf_account'];
								$reply ['type'] = "transfer_customer_service";
							} else {
								// 不是当前分组的就不进行任何操作
							}
						}
					}
					if ($index == 0) {
						$reply ['content'] = "当前频道没有客服在线，请换其他频道咨询！";
						$reply ['type'] = "text";
					}
				}
				break;
			case 5 : // 回复文章消息
				$wz = json_decode ( $info ['content'], true );
				$wzmap ['category_id'] = $wz ['category'];
				$wzmap ['cover_id'] = array (
						"gt",
						0 
				);
				$wzmap ['status'] = 1;
				$wzlist = M ( 'Document' )->where ( $wzmap )->limit ( $wz ['limit'] )->select ();
				$reply ['content'] = $wzlist;
				$reply ['type'] = "news";
				break;
			case 6 : // 回复产品消息
				$wz = json_decode ( $info ['content'], true );
				$wzmap ['category_id'] = $wz ['category'];
				$wzmap ['cover_id'] = array (
						"gt",
						0 
				);
				$wzmap ['status'] = 1;
				$wzlist = M ( 'Product' )->where ( $wzmap )->limit ( $wz ['limit'] )->select ();
				$reply ['content'] = $wzlist;
				$reply ['type'] = "news";
				break;
		}
		return $reply;
	}
}