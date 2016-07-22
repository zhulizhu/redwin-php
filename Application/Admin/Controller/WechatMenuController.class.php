<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

/**
 * 微信自定义菜单控制器
 */
class WechatMenuController extends WechatController {

	/**
	 * 添加自定义菜单
	 */
	public function add($pid=0){
		if(IS_POST){
			$data = $_POST;
			if($data['type']==0){
				$data['content'] = $data['content1'];
			}else{
				$data['content'] = $data['content'][($data['type'])-1];
			}
			$data['status'] = 1;
			$data['wechatid'] = session('wechatid');
			if(M('WechatMenu')->add($data)){
				$this->success('添加成功！',U('Wechat/menu'));
			}else{
				$this->error("添加失败!");
			}
		}else{
			if($pid>0){
				$info = M('WechatMenu')->where('id='.$pid)->find();
				$this->assign('topname',$info['name']);
			}
			$this->assign('pid',$pid);
			$this->meta_title = '自定义菜单管理';
			$this->display('Wechat/edit_menu');
		}
	}
	
	/**
	 * 编辑自定义菜单
	 */
	public function edit($id=0){
		if(IS_POST){
			$data = $_POST;
			if($data['type']==0){
				$data['content'] = $data['content1'];
			}else{
				$data['content'] = $data['content'][($data['type'])-1];
			}
			$data['status'] = 1;
			$data['wechatid'] = session('wechatid');
			if(M('WechatMenu')->save($data)){
				$this->success('修改成功！',U('Wechat/menu'));
			}else{
				$this->error("修改失败!");
			}
		}else{
			if($id>0){
				$info = M('WechatMenu')->where('id='.$id)->find();
				if($info){
					$this->assign("info",$info);
				}else{
					$this->error("获取详细信息出错！");
				}
			}else{
				$this->error("参数错误");
			}
			$this->meta_title = '自定义菜单管理';
			$this->display('Wechat/edit_menu');
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

		$list = M('WechatMenu')->where($map)->order('sort asc')->select();
		$botton = array();
		for($i=0;$i<count($list);$i++){
			$map['pid'] = $list[$i]['id'];
			$sub_botton = M('WechatMenu')->where($map)->order('sort asc')->select();
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

	public function del($id)
	{
		$where =array();
		$where['id'] = $id;
		$res = M("WechatMenu")->where($where)->delete();
		if($res){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}

	public function edit_sort()
	{
		$data = I("post.");
		$res = M("WechatMenu")->save($data);
		if($res!==false){
			$this->success("更新成功！");
		}else{
			$this->error("更新失败！");
		}
	}
	
	public function dataformat($theArray){
		$botton = array();
		$type = $this->casetype($theArray['type']);
		$botton['type'] = $type;
		if($type=="click"){
			$botton['key'] = urlencode($theArray['content']);
		}else{
			if($theArray['type']==5){
				$url = $theArray['content'];
			}else{
				$wechatinfo = M('WechatConfig')->where(array('wechatid'=>get_def_wechatid()))->find();
				$url = "https://open.weixin.qq.com/connect/oauth2/authorize?";
				$url .= "appid=".$wechatinfo['appID']."&";
				$url .= "redirect_uri=".urlencode($theArray['content'])."&";
				$url .= "response_type=code&";
				$url .= "scope=".C('WECHAT_LOGIN_AUTH')."&";
				$url .= "state=STATE#wechat_redirect";
			}
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
			case 5:$type = "view";break;//打开外网
		}
		return $type;
	}
	

}
