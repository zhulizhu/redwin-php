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
class OrderModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
    	array("uid",is_login,3,'function'),
        array('create_time', NOW_TIME),
        array('status', 0, self::MODEL_INSERT),
    );
    
    /**
     * 新增或更新一个文档
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function wcupdate($data){
    	/* 获取数据对象 */
    	$ids = $data['ids'];
    	$data = $this->create($data);
    	if(empty($data)){
    		return false;
    	}
    	/*订单添加 */
    	if(empty($data['id'])){ //新增数据
    		$id = $this->add(); //添加基础内容
    		if(!$id){
    			return false;
    		}else{
    			$where['uid'] = is_login();
    			$where['id'] = array("in",$ids);
    			$cartlist = M('Cart')->where($where)->select();
    			if(!$cartlist){
    				$this->where('id='.$id)->delete();
    				return false;
    			}
    			for($i=0;$i<count($cartlist);$i++){
    				$theArray['order_id'] = $id;
    				$theArray['uid'] = is_login();
    				$theArray['pro_id'] = $cartlist[$i]['pro_id'];
    				$theArray['title'] = $cartlist[$i]['title'];
    				$theArray['picture'] = picture($cartlist[$i]['cover_id']);
    				$theArray['price'] = $cartlist[$i]['price'];
    				$theArray['length'] = $cartlist[$i]['num'];
    				$theArray['status'] = 1;
    				$listid = M('orderlist')->add($theArray);
    				if(!$listid){
    					$this->where('id='.$id)->delete();
    					return false;
    				}
    				/* 修改销量 */
    				$pro_where['id'] = $theArray['pro_id'];
    				M('Product')->where($pro_where)->setInc("xiaoliang",$theArray['length']);
    			}
    			
    			/*提交成功之后清除购物车*/
    			//M('Cart')->where($where)->delete();
    			
    			$wechat = M('WechatConfig')->find();
				$editwcorder['wcorderid'] = $wechat['appID'] . $id;
				$edit_where['id'] = $id;
				$this->where($edit_where)->save($editwcorder);
    		}
    	} else { //更新数据
    		$status = $this->save(); //更新基础内容
    		if(false === $status){
    			return false;
    		}
    	}
    	
    	//内容添加或更新完成
    	return $id;
    }

}
