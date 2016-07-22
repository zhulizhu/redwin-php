<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class ApplyModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
    	array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_BOTH),
		array('status', '1', self::MODEL_BOTH),
    );
    
    /**
     * 新增或更新一个文档
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
    	/* 获取数据对象 */
    	$data = $this->create();
    	if(empty($data)){
    		return false;
    	}
    	/* 添加或新增基础内容 */
    	if(empty($data['id'])){ //新增数据
    		$id = $this->add(); //添加基础内容
    		if(!$id){
    			return false;
    		}
    	} else { //更新数据
    		$status = $this->save(); //更新基础内容
    		if(false === $status){
    			return false;
    		}
    	}
    	//内容添加或更新完成
    	return $data;
    }
    /**
     * 获得品牌名称
     */
    public function getBname($id){
    	if($id!=0){
    		$brand = M('Purcate')->where('id='.$id)->find();
    		return $brand['title'];
    	}
    	
    }
    /**
     * 获得配置车系名称
     */
    public function getCname($id){
    	if($id!=0){
    		$car = M('Purchase')->where('id='.$id)->find();
    		return $car['title'];
    	}
    	 
    }

}
