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
use User\Api\UserApi;

/**
 * 文档基础模型
 */
class OrderModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
    	array("uid",is_login,3,'function'),
        array('create_time', TIME),
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
    	$allcart = json_decode(cookie('cart'),true);
    	$theObj=$allcart["obj"];
    	sort($theObj);
    	/*for ($m=0;$m<count($theObj);$m++){
    		$pro = M('Product')->where('id='.$theObj[$m]["id"])->find();
    		$rest = $pro['total']-$pro['join'];
    		if($theObj[$m]["length"]>$rest){
    			return false;
    		}
    	}*/
    	/*订单添加 */
    	if(empty($data['id'])){ //新增数据
    		$id = $this->add(); //添加基础内容
    		if(!$id){
    			return false;
    		}else{
    			for ($i=0;$i<count($theObj);$i++){
    				$tempArray = M('Product')->where('id='.$theObj[$i]["id"])->find();
    				$theArray['order_id'] = $id;
    				$theArray['uid'] =is_login();
    				$theArray['pro_id'] = $tempArray['id'];
    				$theArray['picture']=$tempArray['cover_id'];
    				$theArray['title'] = $tempArray['title'];
    				$theArray['price'] = $tempArray['price'];
    				for($j=0;$j<$theObj[$i]["length"];$j++){
    					M('orderlist')->add($theArray);
    				}
    			}
    		}
    	}
    	//内容添加或更新完成
    	return $id;
    }

}
