<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

/**
 * 微信多客服分组控制器
 */
class WechatDkhController extends WechatController {

	private $wechat;
	
	public function index(){
		$list = M('WechatDkh')->select();
		$this->assign("list",$list);
		$this->meta_title = "多客服";
		$this->display("Wechat/list_dkh");
	}
	/**
	 * 添加分组
	 */
	public function add($pid=0){
		if(IS_POST){
			$data = I('post.');
			$data['status'] = 1;
			$data['kf_account'] = $data['kf_account'] . "@" . $data['wechatid'];
			unset($data['id']);
			$data['password'] = MD5($data['password']);
			
			//先添加到微信服务器
			$this->wechat = new Wechat; // 实例化 wechat 类
			$dkf_data['kf_account'] = $data['kf_account'];
			$dkf_data['nickname'] = $data['nickname'];
			$dkf_data['password'] = $data['password'];
			$dkf_data =  json_encode($dkf_data);
			$dkf_data = '{"kf_account":"'.$data["kf_account"].'","nickname":"'.$data['nickname'].'","password":"'.$data["password"].'"}';
			$res = $this->wechat->add_dkf($dkf_data);
			if($res['errcode']==0){
				if(M('WechatDkh')->add($data)){
					$this->success("添加成功",U('WechatDkh/index'));
				}else{
					$this->error("添加失败");
				}
			}else{
				$this->error("添加到微信服务器失败！".$this->dfk_error_code($res['errcode']));	
			}
		}else{
			$DkhGroup = M('WechatDkhGroup')->select();
			$WechatConfig = M('WechatConfig')->where('status=1')->select();
			$this->assign("WechatConfig",$WechatConfig);
			$this->assign("DkhGroup",$DkhGroup);
			$this->meta_title = '多客服账号管理';
			$this->display('Wechat/edit_dkf');
		}
	}
	
	public function del($id){
		M('wechat_dkh')->where("id=".$id)->delete();
		$this->success("删除成功！请在公众平台删除真实账号！");
	}
	
	public function dfk_error_code($code){
		$errorcode = array(
			"61451"=>"参数错误",
			"61452"=>"无效客服账号",
			"61453"=>"账号已存在",
			"61454"=>"账号名长度超过限制（前缀10个英文字符）",
			"61455"=>"账号名包含非法字符（英文+数字）",
			"61456"=>"账号个数超过限制（10个客服账号）",
			"61457"=>"无效头像文件类型"
		);
		return $errorcode[$code];
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
