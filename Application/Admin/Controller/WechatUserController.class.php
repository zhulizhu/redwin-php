<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

class WechatUserController extends WechatController {
	# 发送推送
	public function send(){
		if(IS_POST){
			$openid = I('post.openid');
			$msgType = I('post.msgType');
			$data = I('post.');
			unset($data['wechatid'],$data['openid'],$data['msgType']); // 删除多于数据
			$we = new Wechat(); // 实例化 wechat 类
			//$this->error($we->tojson($openid));
			$response = $we->send($openid, $msgType, $data); // 发送推送并返回错误信息
			$response['errcode']==0 ? $this->success($response['errmsg'], str_replace(CONTROLLER_NAME,'Wechat/user',__CONTROLLER__)) :
								   $this->error($response['errmsg'].json_encode($data)); //判断错误信息并返回结果
		}
		else{
			$id = I('get.id',0);
			$user = M('wechat_user')->find($id);
			$material = M('wechat_material as m')->join("LEFT JOIN tb_file as f ON m.fileid=f.id")->select();
			foreach($material as $key=>$val){
				$material[$key]['path']='/uploads/download/'.$val['savepath'].$val['savename'];
			}
			$this->user = $user;
			$this->material = $material;
			$this->meta_title = '发送推送消息';
			$this->display('Wechat:send');
		}
	}
	
	
	/**
	 * 更新用户信息
	 */
	public function upinfo(){
		$user = M(CONTROLLER_NAME);
		$we = new Wechat(); // 实例化 wechat 类
		$newuserlist = $we->userlist();//获取关注用户列表
		$nowuserlist = $user->getField('openid',true);
		if($newuserlist['count']>1000){//循环读取关注用户列表
			$timer = $newuserlist / 1000;
			for($i=0;$i<=$timer;$i++){
				$nlist = $we->userlist($newuserlist['next_openid']);
				array_merge($newuserlist['data']['openid'],$nlist['data']['openid']);
			}
		}
		foreach ($newuserlist['data']['openid'] as $key=>$vo){
			if(!in_array($vo,$nowuserlist)){
				$userinfo = $we->userinfo($vo);
				$userinfo['wechatid'] = session('wechatid');
				$userinfo['addtime'] = NOW_TIME;
				$userinfo['logtime'] = NOW_TIME;
				$user->add($userinfo);
			}else{
				$userinfo = $we->userinfo($vo);
				$user->where("openid=".$vo)->save($userinfo);
			}
		}
		//$return ? $this->success('更新成功') : $this->error($return === false ? '更新失败！': '不需要更新或无法更新');
	}
}