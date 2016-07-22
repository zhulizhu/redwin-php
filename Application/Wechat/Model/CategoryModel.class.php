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

/**
 * 分类模型
 */
class CategoryModel extends Model{

	protected $_validate = array(
		array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
		array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
		array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);

	protected $_auto = array(
		array('model', 'arr2str', self::MODEL_BOTH, 'function'),
		array('model', null, self::MODEL_BOTH, 'ignore'),
		array('extend', 'json_encode', self::MODEL_BOTH, 'function'),
		array('extend', null, self::MODEL_BOTH, 'ignore'),
		array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_BOTH),
		array('status', '1', self::MODEL_BOTH),
	);


	/**
	 * 获取分类详细信息
	 * @param  milit   $id 分类ID或标识
	 * @param  boolean $field 查询字段
	 * @return array     分类信息
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function info($id, $field = true){
		/* 获取分类信息 */
		$map = array();
		if(is_numeric($id)){ //通过ID查询
			$map['id'] = $id;
		} else { //通过标识查询
			$map['name'] = $id;
		}
		return $this->field($field)->where($map)->find();
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
		
		$map  = array('status' => 1);
		$list = $this->field($field)->where($map)->order('sort')->select();
		$list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);
		/* 获取返回数据 */
		if(isset($info)){ //指定分类则返回当前分类极其子分类
			$info = $info['_'] = $list;
			/* 当前分组的分类(不含当前分类) */
			$theArray = $this->getSameSort($id,$field);
			$tempArray = array();
			for($i=0;$i<count($theArray);$i++){
				$map = array('status' => 1,'pid'=>$theArray[$i]['id']);
				$tempArray = $this->field($field)->where($map)->order('sort')->select();
				$theArray[$i]['list'] = $tempArray;
				$info[] = $theArray[$i];
			}
		} else { //否则返回所有分类
			$info = $list;
		}
		return $info;
	}

	/**
	 * 获取指定分类的同级分类
	 * @param  integer $id    分类ID
	 * @param  boolean $field 查询字段
	 * @return array
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>         
	 */
	public function getSameLevel($id, $field = true){
		$info = $this->info($id);
		$map['pid'] = $info['pid'];
		$map['status'] = 1;
		$now = $this->field($field)->where($map)->order('sort')->select();//当前分类的同级分类
		$topSort = $this->getTopId($id);
		$theArray = $this->getSameSort($topSort['id'],$field);
		$tempArray = array();
		for($i=0;$i<count($theArray);$i++){
			$map = array('status' => 1,'pid'=>$theArray[$i]['id']);
			$tempArray = $this->field($field)->where($map)->order('sort')->select();
			$theArray[$i]['list'] = $tempArray;
			$now[] = $theArray[$i];
		}
		return $now;
	}
	
	/**
	 * 自动填充栏目URL
	 * @param unknown $theArray
	 * @return Ambigous <string, unknown>
	 */
	public function getSortUrl($theArray){
		
		$newArray = array();
		foreach ($theArray as $key ){
			/*判断该类别模型，解析模型URL*/
			$model = M('Model');
			$map['id'] = $key['model'];
			$modelname = $model->where($map)->field('name')->find();
			$modelname = $modelname['name'];
			/*------------*/
			
			if($key['pid']==0){
				//封面页
				$key['linkurl'] = U($modelname.'/index?category='.$key['name']);
			}else{
				//列表页
				$key['linkurl'] = U($modelname.'/lists?category='.$key['name']);
			}
			
			//循环递归子级
			if (array_key_exists ( 'list', $key ) && count ( $key ['list'] ) > 0) {
				$key['list'] = $this->getSortUrl($key['list']);
			}
			
			$newArray[] = $key;
		}
		return $newArray;
	}
	
	/**
	 * 自动填充一级单页URL
	 * @param unknown $theArray
	 * @return Ambigous <string, unknown>
	 */	public function getIntroUrl($theArray){
		for($i=0;$i<count($theArray);$i++){
			$map['id'] = $theArray[$i]['category_id'];
			$category = $this->where($map)->field('name')->find();
			$category = $category['name'];
			$theArray[$i]['linkurl'] = U('Article/intro?category='.$category.'&id='.$theArray[$i]['id']);
		}
		return $theArray;		
	}
	
	/**
	 * 获取指定分组的所有分类(不包含当前分类)
	 * @param unknown $cate
	 * @param string $field
	 */
	public function getSameSort($cate,$field = true){
		$info = $this->info($cate);
		$map['pid'] = array('NEQ',$cate);
		$map['group'] = array('like', '%'.$info['title'].'%');
		$theArray = $this->field($field)->where($map)->order('sort')->select();
		return $theArray;
	}

	/**
	 * 更新分类信息
	 * @return boolean 更新状态
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function update(){
		$data = $this->create();
		if(!$data){ //数据对象创建错误
			return false;
		}

		/* 添加或更新数据 */
		return empty($data['id']) ? $this->add() : $this->save();
	}
	
	/**
	 * 获取指定ID的顶级分类信息
	 * @param string $cate 分类ID
	 * @return array 指定ID的顶级分类信息
	 */
	public function getTopId($cate){
		$pid = $cate;
		$temp = array();
		while ( $cate != 0){
			$temp = $this->info($cate);
			$cate = $temp['pid'];
		}
		return $temp;
	}
	
	/**
	 * 逆向获取指定ID的所有分类信息
	 * @param string $cate 分类ID
	 * @return array 分类信息
	 */
	public function getTopDesc($cate){
		$pid = $cate;
		$temp = array();
		while ( $cate != 0){
			$t = $temp[] = $this->info($cate);
			$cate = $t['pid'];
		}
		return $temp;
	}

	/**
	 * 获取指定分类子分类ID
	 * @param  string $cate 分类ID
	 * @return string       id列表
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function getChildrenId($cate){
		$field = 'id,name,pid,title,link_id';
		$category = D('Category')->getTree($cate, $field);
		$ids = array();
		foreach ($category['_'] as $key => $value) {
			$ids[] = $value['id'];
		}
		return implode(',', $ids);
	}

	/**
	 * 查询后解析扩展信息
	 * @param  array $data 分类数据
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	protected function _after_find(&$data, $options){
		/* 分割模型 */
        if(!empty($data['model'])){
            $data['model'] = explode(',', $data['model']);
        }

        /* 分割文档类型 */
        if(!empty($data['type'])){
            $data['type'] = explode(',', $data['type']);
        }

        /* 分割模型 */
        if(!empty($data['reply_model'])){
            $data['reply_model'] = explode(',', $data['reply_model']);
        }

        /* 分割文档类型 */
        if(!empty($data['reply_type'])){
            $data['reply_type'] = explode(',', $data['reply_type']);
        }

        /* 还原扩展数据 */
        if(!empty($data['extend'])){
            $data['extend'] = json_decode($data['extend'], true);
        }
	}

}
