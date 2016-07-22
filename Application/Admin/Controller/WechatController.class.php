<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

class WechatController extends AdminController {
	
	
    public function index(){
		$this->redirect('msg');
    }

    /**
     * 列表后续处理
     */
	private function common($list, $meta_title, $where='', $order='id desc'){
		$list = $list === false ? $this->lists($this->model(), $where ? $where : '', $order) : $list;
		int_to_string($list);
		$this->list = $list;
		//多账户处理
		$moreuser = M('WechatConfig')->where(array('status'=>1))->select();
		$this->moreuser = $moreuser;
		$this->nowwechatid = trim(session('wechatid'));
		$this->meta_title = $meta_title;
		$this->model = $this->model();
		$this->act_name = ACTION_NAME;
        $this->display('Wechat:list_'.ACTION_NAME);
	}
	
	/**
	 * 切换公众号
	 * @param string $wechatid
	 */
	public function changeuser($wechatid=''){
		session('wechatid',$wechatid);
		$this->success("切换账户成功");
	}
	
    public function reply(){
		$this->common(false,'自动回复');
    }
	
    /**
     * 系统参数列表
     */
    public function config(){
        $key = trim(I('get.key'));
		$this->common(false, '系统参数', array('wechatid'=>array('like',"%$key%")));
    }
	
    /**
     * 素材列表
     */
    public function material(){
		$type = trim(I('get.type','news'));
		$file = M('WechatFile');
		if($type=='image' || $type=='voice'){
			$list = $this->lists($file, "filetype='$type'");
			foreach($list as $key=>$val){
				$list[$key]['path'] = __ROOT__.'/uploads/download/'.$val['savepath'].$val['savename']; // 文件地址
			}
		}
		else{
			$list = $this->lists($this->model(), "type='$type'");
			foreach($list as $key=>$val){
				if(json_decode($val['content'])){
					$list[$key]['content'] = json_decode($val['content'],true); // 获取素材内容
					is_array($list[$key]['content'][0]) && $list[$key]['contentCount'] = count($list[$key]['content']); // 获取素材内容个数
				}
				# 获取文件
				if($val['fileid']){
					$info = $file->find($val['fileid']);
					$list[$key]['filename'] = $info['name']; // 文件名
					$list[$key]['path'] = __ROOT__.'/uploads/download/'.$info['savepath'].$info['savename']; // 文件地址
				}
			}
		}
		//print_r($list);
		$this->type = $type;
		$this->common($list,'素材列表');
    }

    /**
     * 日志列表
     */
    public function logs(){
        $key = trim(I('get.key'));
		$msgType = C('msgType');
		$eventType = C('eventType');
		$user = M('wechat_user');
		$list = $this->lists($this->model(), $key ? array('content'=>array('like',"%$key%")) : '', 'id desc');
		foreach($list as $key=>$val){
			$arr = json_decode($val['content'],true);
			$info = $user->where("wechatid='".$arr['wechatid']."' and openid='".$arr['openid']."'")->find();
			foreach($info as $k=>$v){
				$k == 'id' && $k = 'userid';
				$list[$key][$k] = $v;
			}
			foreach($arr as $k=>$v){
				$list[$key][$k] = $v;
			}
			switch($list[$key]['MsgType']){
				case Wechat::MSGTYPE_TEXT:$content = $list[$key]['Content'];break;
				case Wechat::MSGTYPE_VOICE:$content = $list[$key]['MsgId'];break;
				case Wechat::MSGTYPE_EVENT:
					$content = $list[$key]['EventKey'];
					$event = $eventType[$list[$key]['Event']];
				break;
			}
			$list[$key]['MsgType'] = $msgType[$list[$key]['MsgType']];
			$list[$key]['Event']   = $event ? $event : '';
			$list[$key]['content'] = $content;
		}
		$this->common($list, '日志列表');
    }
	
    public function mass(){
		$this->common('群发消息');
    }
    
    public function jxs(){
    	$content = M('WechatReply')->where('id=5')->getField('text');
    	$this->assign('content',$content);
    	$this->display();
    }
    
    
	
    public function menu(){
    	$wechatid = get_def_wechatid();
    	$tree = $this->get_menutree();
		$this->assign('tree',$tree);
		$this->assign('moreuser',$this->get_list());
		$this->meta_title = '自定义菜单管理';
		$this->display("list_menu");
    }
    
    /**
     * 获取所有微信用户
     */
    public function get_list(){
    	$list = M('WechatConfig')->where('status=1')->select();
    	return $list;
    }
    
    /**
     * 获取自定义菜单树
     * @return unknown
     */
    public function get_menutree(){
    	$map['wechatid'] = session('wechatid');
    	$map['pid'] = 0;
    	$map['status'] = 1;
    	$list = M('WechatMenu')->where($map)->order('sort asc')->select();
    	for($i=0;$i<count($list);$i++){
    		$temp = M('WechatMenu')->where('pid='.$list[$i]['id'])->order("sort asc")->select();
    		if(count($temp)>0){
    			$list[$i]['sub_button'] = $temp;
    		}
    	}
    	return $list;
    }
    
    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function menutree($tree = null){
    	$this->assign('tree', $tree);
    	$this->display('tree');
    }

    /**
     * 消息列表
     */
    public function msg(){
        $key = trim(I('get.key'));
		$msgType = C('msgType');
		$user = M('wechat_user');
		$list = $this->lists('wechat_logs', $map = "content like '%$key%' and content not like '%event%'", 'id desc');
		foreach($list as $key=>$val){
			$arr = json_decode($val['content'],true);
			$info = $user->where("wechatid='".$arr['wechatid']."' and openid='".$arr['openid']."'")->find();
			unset($info['addtime']);
			foreach($info as $k=>$v){
				$k == 'id' && $k = 'userid';
				$list[$key][$k] = $v;
			}
			foreach($arr as $k=>$v){
				$list[$key][$k] = $v;
			}
			$list[$key]['msgType'] = $msgType[$list[$key]['MsgType']];
		}
		$this->common($list,'消息列表');
    }

    /**
     * 用户列表
     */
    public function user(){
    	$wechatid = session('wechatid');
    	$wechatid = empty($wechatid) ? 0 : session('wechatid');
    	if($wechatid!=0) $map['wechatid'] = $wechatid;
    	$key = trim(I('get.key'));
        $map['nickname'] = array('like',"%$key%");
		$this->common(false,'用户列表', $map);
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model=''){
        return parent::setStatus($model);
    }

    /**
     * 删除
     */
    public function del($model=''){
        $ids = array_unique(array_filter((array)I('ids')));
        if ( empty($ids) ) {
            $this->error('请选择要操作的数据!');
        }
        $map = array('id' => array('in', $ids) );
        if(M($model)->where($map)->delete()){
            //记录行为
            //action_log('update_'.$model, 'Wechat', $ids, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
    public function change(){
		$data['text'] = I('post.content');
		$result = M('WechatReply')->where('id=5')->save($data);
		if($result){
			$this->success('数据更新成功');
		}else {
			$this->error('数据更新失败');
		}
		
    }

    /**
     * 获取 model
     */
	private function model(){
		return CONTROLLER_NAME.'_'.ACTION_NAME;
	}
}