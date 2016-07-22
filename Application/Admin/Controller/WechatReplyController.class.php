<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

/**
 * 微信自动回复控制器
 */
class WechatReplyController extends WechatController {

	/**
	 * 添加自动回复
	 */
	public function add($pid=0){
		if(IS_POST){
			$data = $_POST;
			$data['status'] = 1;
			//$data['wechatid'] = session('wechatid');先容我考虑考虑要不要做成多账号
			$data['text'] = $data['content'][$data['material']];
			unset($data['id']);
			if($data['material']==1){
				$data['sc_id'] = $data['sc_id'][$data['sc_type']];
			}
			if($data['material']==2){
				$data['text'] = json_encode($data['wenzhang']);
			}
			if(M('WechatReply')->add($data)){
				$this->success('添加成功！',U('Wechat/reply'));
			}else{
				$this->error("添加失败!");
			}
		}else{
			$this->meta_title = '添加自动回复';
			$this->assign("sclist",$this->sclist());
			$tree = D('Category')->getTree();
			$this->assign("tree",$tree);
			
			$DkhGroup = M('WechatDkhGroup')->select();
			$this->assign("DkhGroup",$DkhGroup);
			
			$this->display('Wechat/edit_reply');
		}
	}
	
	public function del($id){
		$where['id'] = $id;
		$id = M('WechatReply')->where($where)->delete();
		if($id){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
		exit;
	}
	
	//获取素材列表
	public function sclist(){
		$list = M('WechatFile')->select();
		for($i=0;$i<count($list);$i++){
			$map['fileid'] = $list[$i]['id'];
			$media = M('WechatFileMedia')->where($map)->select();
			$list[$i]['media'] = $media;
		}
		return $list;
	}
	
	/**
	 * 编辑自定义菜单
	 */
	public function edit($id=0){
		if(IS_POST){
			$data = I("post.");
			$res = M('WechatReply')->where("id=".$data['id'])->save($data);
			if($res!==false){
				$this->success('修改成功！',U('Wechat/reply'));
			}else{
				$this->error("修改失败!");
			}
		}else{
			if($id>0){
				$info = M('WechatReply')->where('id='.$id)->find();
				if($info){
					$this->assign("info",$info);
				}else{
					$this->error("获取详细信息出错！");
				}
			}else{
				$this->error("参数错误");
			}
			$this->meta_title = '自动回复管理';
			$this->display('Wechat/edit_reply');
		}
	}
	
	
	/**
	 * 发布自定义菜单
	 * 
	 * @author 智网天下科技 http://www.cheewo.com
	 */
	public function post(){
		
		$wechatid = session('wechatid');
		$map['wechatid'] = $wechatid;
		$map['status'] = 1;
		$map['pid'] = 0;
		$list = M('WechatMenu')->where($map)->order('orderby')->select();
		$botton = array();
		for($i=0;$i<count($list);$i++){
			$map['pid'] = $list[$i]['id'];
			$sub_botton = M('WechatMenu')->where($map)->order('orderby')->select();
			if(count($sub_botton)>0){
				$botton[$i]['name'] = urlencode($list[$i]['name']);
				for($j=0;$j<count($sub_botton);$j++){
					$botton[$i]['sub_button'][$j] = $this->dataformat($sub_botton[$j]);
				}
			}else{
				$botton[$i] = $this->dataformat($list[$i]);
			}
		}
		
		$theArray['button'] = $botton;
		$menu = json_encode($theArray);
		$menu = urldecode ( $menu );	
		$this->wechat = new Wechat; // 实例化 wechat 类
		$r = $this->wechat->menu_create($menu);
		if($r['errcode']==0){
			$this->success('发布成功！',U('Wechat/menu'));
		}else{
			$this->error('发布失败！',U('Wechat/menu'));
		}
	}
	
	public function dataformat($theArray){
		$botton = array();
		$type = $this->casetype($theArray['type']);
		$botton['type'] = $type;
		if($type=="click"){
			$botton['key'] = urlencode($theArray['content']);
		}else{
			$wechatinfo = M('WechatConfig')->where(array('wechatid'=>session('wechatid')))->find();
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?";
			$url .= "appid=".$wechatinfo['appID']."&";
			$url .= "redirect_uri=".$theArray['content']."&";
			$url .= "response_type=code&";
			$url .= "scope=snsapi_base&";
			$url .= "state=STATE#wechat_redirect";
			$botton['url'] = urlencode ($url);
		}
		$botton['name'] = urlencode ($theArray['name']);
		return $botton;
	}
	
	/**
	 * 选取类型
	 * @param unknown $type
	 * @return string
	 */
	public function casetype($type){
		switch ($type){
			case 0:$type = "click";break;//自定义消息
			case 1:$type = "click";break;//素材消息
			case 2:$type = "view";break;//拨打电话
			case 3:$type = "view";break;//发送地图
			case 4:$type = "view";break;//打开网站
			case 5:$type = "click";break;//人工接入
		}
		return $type;
	}
	

}
