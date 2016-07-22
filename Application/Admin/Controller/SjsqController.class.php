<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 分佣详情控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class SjsqController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index($uid=0){
    	
    	$where = array();
    	if($uid!=0){
    		$where['uid'] = $uid;
    	}
    	$list = $this->lists("upapply",$where);
    	$this->assign("list",$list);
    	
    	$this->meta_title = '升级申请';
    	$this->display();
    }
    
    public function tongguo($id){
    	$info = M('upapply')->where('id='.$id)->find();
    	
    	$shangji = M('auth_group_access')->where("uid=".$info['uid'])->getField("puid");
    	$sjgroup = get_group_by_uid($shangji);
    	$sjgroupid = $sjgroup['id'];//原上级分组
    	
    	
    	$auth = M('auth_group_access')->where("uid=".$info['uid'])->find();

    	/* 从未有过追平 */
    	if($auth['zp']==0){
    		/* 如果升级满足追平条件 */
    		if($sjgroupid==$info['to_group_id']){
    			$data['zp'] = 1;
    		}
    	}else{
    		/* 如果升级满足脱离条件 */
    		if($sjgroupid==$info['to_group_id']){
    			$p = M('auth_group_access')->where("uid=".$auth['puid'])->getField("puid");
    			M('auth_group_access')->where("uid=".$info['uid'])->setField("puid",$p);
    			//记得要把追平状态还原哦，没设置之前等于还把超越后的上级追平了，囧
    			M('auth_group_access')->where("uid=".$info['uid'])->setField("zp",0);//设置追平状态
    		}
    	}
    	
    	$data['group_id'] = $info['to_group_id'];
    	$where['uid'] = $info['uid'];
    	$res = M('auth_group_access')->where($where)->save($data);
    	if($res){
    		$map = array();
    		$map['puid'] = $info['uid'];
    		$map['zp'] = 1;
    		M("auth_group_access")->where($map)->setField("zp",0);
    		
    		M('upapply')->where('id='.$id)->delete();
    		$this->success("通过申请！");
    	}else{
    		$this->error("通过失败！");
    	}
    }
    
    public function jujue($id){
    	$res = M('upapply')->where('id='.$id)->delete();
    	if($res){
    		$this->success("拒绝成功！");
    	}else{
    		$this->error("拒绝失败！");
    	}
    }

}
