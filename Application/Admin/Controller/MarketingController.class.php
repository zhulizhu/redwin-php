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
 * 营销控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class MarketingController extends AdminController {

    /**
     * 营销规则
     * @return none
     */
    public function index(){
    	$list = $this->lists ( 'Marketing' );
    	$this->assign("list",$list);
    	
    	$prolist = M('Product')->field("id,title")->order("id desc")->select();
    	
    	
    	$this->assign("prolist",$prolist);
    	
        $this->meta_title = '营销规则';
        $this->display();
    }
    
    public function add(){
    	
    	if(IS_POST){
    		
    		$data = I('post.');
    		$data['start_time'] = strtotime($data['start_time']);
    		$data['end_time'] = strtotime($data['end_time']);
    		$id = M('Marketing')->add($data);
    		if($id){
    			$this->success("添加规则成功！",U('index'));
    		}else{
    			$this->error("添加规则失败！",U('index'));
    		}
    	}else{
    		$this->meta_title = '新增规则';
    		$this->display("edit");
    	}

    }
    
    public function edit($id){
    	
    	if(IS_POST){
    		$data = I('post.');
    		$data['start_time'] = strtotime($data['start_time']);
    		$data['end_time'] = strtotime($data['end_time']);
    		$id = M('Marketing')->save($data);
    		if($id>=0){
    			$this->success("修改成功！",U('index'));
    		}else{
    			$this->error("修改失败！",U('index'));
    		}
    	}else{
    		$info = M('Marketing')->where("id=".$id)->find();
    		
    		$info['start_time'] = time_format($info['start_time'],"Y-m-d H:i");
    		$info['end_time'] = time_format($info['end_time'],"Y-m-d H:i");
    		
    		$this->assign("info",$info);
    		$this->meta_title = '编辑规则';
    		$this->display("edit");
    	}
    	
    }

	public function del($id)
	{
		$res = M("marketing")->where("id=".$id)->delete();
		if($res!==false){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
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
