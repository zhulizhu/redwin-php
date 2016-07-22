<?php
namespace Home\Controller;
use Think\Controller;
use Common\Controller\Wechat;

/**
 * 微信控制器
 */
class WechatController extends HomeController {
	
	private $wechat;
	private $gain;
	/**
	 * 校验 token 、实例化 wechat 类、获取 post数据、写入日志
	 */
	public function __construct(){
		$this->wechat = new Wechat; // 实例化 wechat 类
		$this->gain = $this->wechat->_receive; // 存入 接收到的数据
		$this->gain['wechatid'] = $this->gain['ToUserName'];
		$this->gain['openid'] = $this->gain['FromUserName'];
		$this->gain['logtime'] = NOW_TIME;
		$this->wechat->valid() && M("wechat_logs")->add(array('content'=>json_encode($this->gain), 'addtime'=>NOW_TIME)); // 插入日志
	}
	/**
	 * 接收所有信息
	 */
	public function index(){
		# 添加、更新用户
		$user = M('wechat_user'); // 实例化 WechatUser 对象
		$user_id = $user->field('id')->where("wechatid='".$this->gain['wechatid']."' and openid='".$this->gain['openid']."'")->find();
		if(0 < $user_id['id']){ // 存在该用户
			$user->where("id='".$user_id['id']."'")->save($this->gain); // 更新
		}
		else{
			$this->gain['addtime'] = NOW_TIME;
			$data=array_merge($this->gain,$this->wechat->userinfo($this->gain['openid'])); // 将用户信息合并
			$user->add($data); // 添加
		}
		
		# 根据不同的消息类型，进行不同的操作
		$mtype = $this->wechat->getRevType(); // 获取消息类型
		$tb_reply = M('wechat_reply as r'); // 实例化 wechat_reply
		switch($mtype) {
			# 事件消息
			case Wechat::MSGTYPE_EVENT:
				$event=$this->wechat->getRev()->getRevEvent(); // 获取事件类型
				
				// 关注事件
				if($event['event']=='subscribe'){ 
					$subscribe = M('WechatReply')->where(array('type'=>'subscribe'))->find();
					$reply['content'] = $subscribe['text'];
					$reply['type'] = 'text';
					//$reply = $tb_reply->field("IFNULL(m.content,r.text) AS content,IFNULL(m.type,'text') AS type")->join('LEFT JOIN tb_wechat_material AS m ON r.material = m.id')->where("r.type='".Wechat::REPLY_ADD."' and r.status=1")->find(); // 获取关注事件的推送
				}
				/*扫描二维码事件*/
				if($event['event']=="SCAN"){
					/*扫描二维码登录网站*/
					if(strlen($event['key']) == 9 && substr($event['key'],0,2) == "88"){
						$theArray['openid'] = $this->wechat->getRevFrom();
						$theArray['status'] = 1;
						M('WechatTempLog')->where(array('scene'=>$event['key']))->save($theArray);
						
						$info = M('UcenterMember')->where(array('open_id'=>$this->wechat->getRevFrom(),'status'=>1))->find();
						
						if(count($info)>0){
							$reply['content'] = "二维码扫描登录成功！";
						}else{
							$reply['content'] = "您还未绑定账号，请到我的智网-会员中心绑定";
						}
					}else{
						$reply['content'] = strlen($event['key']) . "-----" . substr($event['key'],0,2);
					}
					$reply['type'] = 'text';
				}
				/*请求人工接入*/
				if($event['event'] == "CLICK" && $event['key']=="jieru"){
					$reply['content'] = "您好，请回复任意文字后将为您接入人工坐席！";
					$reply['type'] = "text";
				}
				
			break;
			# 文本消息
			case Wechat::MSGTYPE_TEXT:
				$msg = $this->gain['Content']; // 获取消息内容
				$result = $tb_reply->where("r.type='".Wechat::REPLY_KEY."' and r.status=1")->select(); // 获取回复类型 数据集
				$total = 0; // 初始化 查找到的关键词总数
				foreach($result as $val){ // 智能判断 关键词
					$num = 0;
					foreach(explode("\r\n",$val['keyword']) as $k=>$v){ // 将关键词转换为数组
						strlen(stristr($msg,$v)) && $num+=strlen($v); // 在 用户发送的消息 中查找 关键词，找到就叠加
					}
					$total+=$num; // 总数累计
					$kss[$num] = $val['material']; // 分组统计在不同规则中 查找到的关键词总数
				}
				ksort($kss); // 根据键名（不同规则中 查找到的关键词总数）排序
				$total > 0 && $reply = M('wechat_material')->field('type,content')->where('id='.end($kss))->find();// 传入返回素材ID，并获取返回内容
				
				$reply['content'] = "请稍等，正在为您接入人工坐席！";
				$reply['type'] = "transfer_customer_service";
			break;
			default:
			//$reply = $tb_reply->join('LEFT JOIN tb_wechat_material AS m ON r.material = m.id')->where("r.type='".Wechat::REPLY_AUTO."' and r.status=1")->find(); // 获取其他事件的推送
		}
		
		//$this->wechat->reply($this->wechat->tojson($reply), 'text');die;
		//$data = $reply['content'] ? $reply['content'] : $reply['text'];
		//$msgType = $reply['content'] ? $reply['type1'] : 'text';
		$reply && $this->wechat->reply($reply['content'], $reply['type']); // 返回推送
		
		
	}
	
	

}