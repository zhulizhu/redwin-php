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
 * 文档基础模型
 */
class AddressModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
    	array("uid",is_login,3,'function'),
        array('create_time', NOW_TIME),
    	array('is_def',1,self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
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
    		$map['uid'] = is_login();
    		$save['is_def'] = 0;
    		$this->where($map)->save($save);
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

}
