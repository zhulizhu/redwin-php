<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Manager\Model;
use Think\Model;

/**
 * 联动菜单模型
 */
class ParametersModel extends Model{

    /* 自动完成规则 */
    protected $_auto = array(
    	array('name', 'strtolower', self::MODEL_INSERT, 'function'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT, 'string'),
    );
    
    /**
     * 查询一条信息
     * @param unknown $id
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, unknown, string, mixed>
     */
    public function info($id){
    	return $this->find($id);
    }
    
    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true){
    	/* 获取当前分类信息 */
    	if($id){
    		$info = $this->info($id);
    		$id   = $info['id'];
    	}
    	/* 获取所有分类 */
    	$map  = array('status' => array('gt', -1));
    	$field = "id,pid,title";
    	$list = $this->field($field)->where($map)->order('id')->select();
    	$list = list_to_tree($list);

    	/* 获取返回数据 */
    	if(count($info)>0){ //指定分类则返回当前分类极其子分类
    		$info['_child'] = $list;
    	} else { //否则返回所有分类
    		$info = $list;
    	}
    	return $info;
    }

/**
     * 新增或更新一个分类
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        /* 获取数据对象 */
    	$data = $this->create();
        if(empty($data)){
            return false;
        }
        $data['sort'] = implode(",",$data['sort']);
        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
        	$title = explode(PHP_EOL,$data['title']);
        	for($i=0;$i<count($title);$i++){
        		if(!empty($title[$i])){
        			$data['title'] = trim($title[$i]);
        			$id = $this->add($data); //添加基础内容
        			if(!$id){
        				$this->error = '新增模型出错！';
        				return false;
        			}
        		}
        	}
            
        } else { //更新数据
            $status = $this->save($data); //更新基础内容
            if(false === $status){
                $this->error = '更新模型出错！';
                return false;
            }
        }

		//记录行为
		//action_log('update_model','model',$data['id'] ? $data['id'] : $id,UID);

        //内容添加或更新完成
        return $data;
    }
}
