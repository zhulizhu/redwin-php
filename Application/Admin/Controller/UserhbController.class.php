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
 * 用户红包控制器
 */
class UserhbController extends AdminController {

    /**
     * 发出去的红包
     * @return none
     */
    public function index(){
    	
    	$map = array();
    	
    	if (I ( 'uid' ) && $_GET ['uid'] != "请输入用户编号") {
    		$map ["uid"] = I ( 'uid' );
    	}
    	
    	if(I('time-start') && I('time-end') &&  $_POST['time-start']!="结束时间" && $_POST['time-start']!="起始时间"){
    		$map['create_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
    	}
    	
    	if (I ( 'hb_type' )) {
    		$map ["hb_type"] = I ( 'hb_type' );
    	}
    	
    	$list = $this->lists ( 'userhb' ,$map );
    	$this->assign("list",$list);
        $this->meta_title = '发出的红包';
        $this->display();
    }
    
    public function add(){
    	
    	if(IS_POST){
    		
    		$data = I('post.');
    		$data['start_time'] = strtotime($data['start_time']);
    		$data['end_time'] = strtotime($data['end_time']);
    		$data['group'] = implode(",",$data['group']);
    		$id = M('coupons')->add($data);
    		if($id){
    			$this->success("添加优惠券成功！",U('index'));
    		}else{
    			$this->error("添加优惠券失败！",U('index'));
    		}
    	}else{
    		
    		$grouplist = M('auth_group')->field("id,title")->select();
    		$this->assign("grouplist",$grouplist);
    		
    		$this->meta_title = '新增优惠券';
    		$this->display("edit");
    	}

    }
    
    
    public function lingqu(){
    	$map = array();
    	 
    	if (I ( 'uid' ) && $_GET ['uid'] != "请输入用户编号") {
    		$map ["uid"] = I ( 'uid' );
    	}
    	 
    	if(I('time-start') && I('time-end') &&  $_POST['time-start']!="结束时间" && $_POST['time-start']!="起始时间"){
    		$map['create_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
    	}
    	 
    	if (I ( 'status' )) {
    		$map ["status"] = I ( 'status' );
    	}
    	
    	$list = $this->lists("userhb_log",$map);
    	
    	for($i=0;$i<count($list);$i++){
    		
    		$list[$i]['puid'] = M('userhb')->where("id=".$list[$i]['pid'])->getField("uid");
    		
    	}
    	
    	$this->assign("list",$list);
    	$this->meta_title = '领取记录';
    	$this->display();
    }
    
    public function uselog(){
    	
    	$map = array();
    	
    	if (I ( 'uid' ) && $_GET ['uid'] != "请输入用户编号") {
    		$map ["uid"] = I ( 'uid' );
    	}
    	
    	if(I('time-start') && I('time-end') &&  $_POST['time-start']!="结束时间" && $_POST['time-start']!="起始时间"){
    		$map['create_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
    	}
    	
    	
    	$map['status'] = 0;
    	$list = $this->lists("userhb_log",$map);
    	for($i=0;$i<count($list);$i++){
    		
    	}
    	
    	$this->assign("list",$list);
    	 
    	$this->meta_title = '使用记录';
    	$this->display();
    }
    
    public function edit($id){
    	
    	if(IS_POST){
    		$data = I('post.');
    		$data['start_time'] = strtotime($data['start_time']);
    		$data['end_time'] = strtotime($data['end_time']);
    		$id = M('coupons')->save($data);
    		if($id>=0){
    			$this->success("修改成功！",U('index'));
    		}else{
    			$this->error("修改失败！",U('index'));
    		}
    	}else{
    		$info = M('coupons')->where("id=".$id)->find();
    		$info['start_time'] = time_format($info['start_time'],"Y-m-d H:i");
    		$info['end_time'] = time_format($info['end_time'],"Y-m-d H:i");
    		$this->assign("info",$info);
    		$this->meta_title = '编辑优惠券';
    		$this->display("edit");
    	}
    	
    }
    
    public function get_status($pid,$proid){
    	$where['pid'] = $pid;
    	$where['proid'] = $proid;
    	$status = M('MarketingList')->where($where)->getField("status");
    	if($status==1){
    		echo "已启用";
    	}else{
    		echo "已停用";
    	}
    	exit;
    }
    
    public function get_new_price($pid,$proid){
    	
    	$where['pid'] = $pid;
    	$where['proid'] = $proid;
    	$info = M('MarketingList')->where($where)->find();
    	if($info){
    		header('Content-Type: application/json');
    		echo json_encode($info);
    	}else{
    		echo "0";
    	}
    	exit;
    }
    
    public function update_list(){
    	
    	if(IS_POST){
    		
    		$data = I('post.');
    		
    		$where['pid'] = $data['pid'];
    		$where['proid'] = $data['proid'];
    		$info = M('MarketingList')->where($where)->find();
    		if($info){
    			$data['id'] = $info['id'];
    			$id = M('MarketingList')->save($data);
    			if($id>=0){
    				echo "2";
    			}else{
    				echo "-2";
    			}
    		}else{
    			$id = M('MarketingList')->add($data);
    			if($id){
    				echo "1";
    			}else{
    				echo "0";
    			}
    		}
    		
    		
    		exit;
    	}
    	
    }

    
}
