<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

/**
 * 联动菜单模型
 */
class LinkageModel extends Model{

    /* 自动完成规则 */
    protected $_auto = array(
    	array('name', 'strtolower', self::MODEL_INSERT, 'function'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
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
    public function lists($sort){
    	$map['sort']= array("like","%".$sort."%");
    	$map['pid']=0;
    	$map['status']=1;
    	$sortlist = $this->where($map)->select();
    	return $sortlist;
    }
    
    public function listse($sort){
    	$map['pid']=$sort;
    	$map['status']=1;
    	$sortlist = $this->where($map)->select();
		for($i=0; $i<count($sortlist); $i++){
			if($sortlist[$i]["id"]==2){
				unset($sortlist[$i]);
			}
		}
    	return $sortlist;
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
            $status = $this->save(); //更新基础内容
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
