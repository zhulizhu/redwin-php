<?php
namespace Wechat\Model;
use Think\Model;

/**
 * 收藏模型
 */
class LikeModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
    	array("uid",is_login,3,'function'),
        array('create_time', NOW_TIME),
    );
    
    public function update(){
    	/* 获取数据对象 */
    	$data = $this->create();
    	dump($data);
    	exit;
    	if(empty($data)){
    		return false;
    	}
    	if(empty($data['id'])){ //新增数据
    		$id = $this->add($data);
    		if(!$id){
    			$this->error = "添加收藏失败!";
    			return false;
    		}
    	}else{
    		$status = $this->save();
    		if(false === $status){
    			$this->error = '修改出错！';
    			return false;
    		}
    	}
    }
    
}
