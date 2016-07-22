<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wechat\Model;
use Think\Model;
use User\Api\UserApi;

/**
 * 购物车模型
 */
class CartModel extends Model{

    /* 购物车模型自动完成 */
    protected $_auto = array(
    	array("uid",is_login,3,'function'),
        array('add_time',  NOW_TIME , 1),
    	array('update_time',NOW_TIME,3)
    );
    
    /**
     * 更新购物车 添加或删除
     * @return boolean
     * @author 智网天下科技 http://www.cheewo.com
     */
    public function update($data){
    	
    	$data = $this->create($data);
    	if($data['num']<=0){
    		$this->error = "数量不能小于0";
    		return false;
    	}
    	$where['uid'] = is_login();
    	$where['pro_id'] = $data['pro_id'];
    	$where['save_type'] = 0;
    	$data['save_type'] = 0;
    	$info = $this->where($where)->find();
    	if($info){
    		$save_data['num'] = $data['num'];
    		$save_where['id'] = $info['id'];
    		$status = $this->where($save_where)->save($save_data);
    		if($status===false){
    			$this->error = "更新购物车失败！";
    			return false;
    		}
    	}else{
    		$id = $this->add();
    		if(!$id){
    			$this->error = "添加购物车失败！";
    			return false;
    		}
    	}
    	
    	return true;
    	
    }
    
    public function buynow_cart($data){
    	$data = $this->create($data);
		$where = array();
    	$where['uid'] = is_login();
    	$where['pro_id'] = $data['pro_id'];
    	$where['save_type'] = 1;
    	$info = $this->where($where)->delete();
		$data['save_type'] = 1;
		$id = $this->add($data);
    	return true;
    }
    
    /**
     * 获取当前用户的总数量
     * @return number
     * @author 智网天下科技 http://www.cheewo.com
     */
    public function get_count($ids='',$save_type=0){
    	/* 查找当前用户的购物车 */
    	if($ids!=''){
    		$where['id'] = array('in',$ids);
    	}
    	$where['uid'] = is_login();
    	$where['save_type'] = $save_type;
    	$ids = $this->where($where)->getField("id",true);
    	if(!$ids) return 0;
    	/*统计这些产品的数量*/
    	$num_where['id'] = array('in',implode(",",$ids));
    	$num = $this->where($num_where)->getField("num",true);
    	/* 求和并返回 */
    	return array_sum($num);
    }
    
    /**
     * 获取当前用户的总价格
     * @return number
     * @author 智网天下科技 http://www.cheewo.com
     */
    public function get_money($ids='',$save_type=0){
    	/* 查找当前用户的购物车 */
    	$where['uid'] = is_login();
    	if(isset($_REQUEST['save_type']) && $_REQUEST['save_type']==1){
    		$where['save_type'] = 1;
    	}else{
    		$where['save_type'] = 0;
    	}
    	if($save_type==1){
    		$where['save_type'] = 1;
    	}
    	if($ids!=''){
    		$where['id'] = array('in',$ids);
    	}
    	$list = $this->where($where)->field('num,price')->select();
    	if(!count($list)){
    		return 0;
    	}
    	for($i=0;$i<count($list);$i++){
    		$xiaoji[] = $list[$i]['num'] * $list[$i]['price'];
    	}
    	return array_sum($xiaoji)*100;
    }
    

}
