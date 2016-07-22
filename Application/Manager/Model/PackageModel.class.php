<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Manager\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class PackageModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('create_time', NOW_TIME),
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
    	/*订单添加 */
    	if(empty($data['id'])){ //新增数据
    		$id = $this->add(); //添加基础内容
    		if(!$id){
    			return false;
    		}else{
    			$allcart = json_decode(cookie('package'),true);
    			$allcart=array_values($allcart);
    			$Document = D('Document');
    			for ($i=0;$i<count($allcart);$i++){
    				$theArray = $Document->detail($allcart[$i]["id"]);
    				$theArray['packid'] = $id;
    				$theArray['title'] = $theArray['title'];
    				$theArray['picture']=$theArray['cover_id'];
    				$theArray['price']=$theArray['price'];
    				unset($theArray['id']);
    				M('packagelist')->add($theArray);
    			}
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
