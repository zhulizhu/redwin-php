<?php
// +----------------------------------------------------------------------
// | CheeWoPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Think\Page;

/**
 * 广告控制器
 * @author lihao <lihao@cheewo.com>
 */
class AdsController extends ManagerController {

    /**
     * 广告分类管理
     * @author lihao <lihao@cheewo.com>
     */
    public function index(){
    	$list = $this->lists('AdsSort',array('status'=>1));
    	$this->assign('_list',$list);
    	$this->meta_title = '广告分类管理';
    	$this->display();
    }
    
    /**
     * 添加广告分类
     */
    public function AddSort(){
    	if(IS_POST){
    		$res = D('AdsSort')->update();
    		if($res){
    			$this->success($res['id']?'更新成功':'新增成功',U('index'));
    		}
    	}else{
    		$this->meta_title = '新增分类';
    		$this->display('editsort');
    	}
    }
    
    /**
     * 编辑广告分类
     */
    public function EditSort($id=0){
    	$info = D('AdsSort')->info($id);
    	$this->assign('info',$info);
    	$this->meta_title = '编辑分类';
    	$this->display('editsort');
    }
    
    /**
     * 删除广告分类
     */
    public function delsort(){
    	$id = array_unique((array)I('ids',0));
    	if ( empty($id) ) {
    		$this->error('请选择要操作的数据!');
    	}
    	$map = array('id' => array('in', $id) );
    	if(M('AdsSort')->where($map)->delete()){
    		$this->success('删除成功');
    	} else {
    		$this->error('删除失败！');
    	}
    }
    
    /**
     * 广告列表
     * @param number $id
     */
    public function Adlist($id = 0){
    	$where['status'] = 1;
    	$where['pid'] = $id;
    	$list = $this->lists('Ads',$where);
    	int_to_string($list);
    	$this->assign('_list',$list);
    	$info = D('AdsSort')->info($id);
    	$this->assign('sort',$info);
    	$this->assign('pid',$id);
    	$this->display();
    }
    
    public function add($pid=0,$id=0){
    	if(IS_POST){
    		$res = D('Ads')->update();
    		if($res){
    			$this->success($res['id']?'更新成功':'新增成功',U('Ads/Adlist?id='.$_REQUEST['pid']));
    		}
    	}else{
    		$info = D('AdsSort')->info($pid);
    		$this->assign('sort',$info);
    		$this->assign('pid',$pid);
    		if($id>0){
    			$info = M('Ads')->where('id='.$id)->find();
    			$this->assign('info',$info);
    			$this->meta_title = '编辑广告';
    			$this->display('edit');
    		}else{
    			$this->meta_title = '新增广告';
    			$this->display('edit');
    		}
    		
    	}
    }
    
    /**
     * 删除广告
     */
    public function del(){
    	$id = array_unique((array)I('ids',0));
    	if ( empty($id) ) {
    		$this->error('请选择要操作的数据!');
    	}
    	$map = array('id' => array('in', $id) );
    	if(M('Ads')->where($map)->delete()){
    		$this->success('删除成功');
    	} else {
    		$this->error('删除失败！');
    	}
    }

}
