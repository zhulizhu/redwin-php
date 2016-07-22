<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Manager\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PackageController extends ManagerController {

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        if(UID){
            $this->meta_title = '管理首页';
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }
    
	//添加套餐
	public function update(){
		//添加或修改
		$Address = D('Package');//调用模型
		$res = $Address->update();
		dump($res);
		echo 1;
		exit();
		if($res){
			$allcart = json_decode(cookie('$allcart'),true);
			$allcart=null;//订单添加成功后清空cookie
			cookie('package',json_encode($allcart));
			 $this->error('新增成功');
		}else {
			$this->error('错误');
		}
	}
    
    //删除cookie其中的一项
   	public function del($id=0){
   		$allcart = json_decode(cookie('package'),true);
   		$allcart=array_values($allcart);
   		for ($i=0;$i<count($allcart); $i++){
   			if ($allcart[$i]["Mark"]==$id) {
   				unset($allcart[$i]);
   			}
   		}
   		$allcart =json_encode($allcart);
		cookie('package',$allcart);
   		$this->redirect('add');
   	}
    //遍历cookie读出来
    public function add(){
    	$allcart = json_decode(cookie('package'),true);
    	$Document = D('Document');
    	$allcart=array_values($allcart);
    	/*调去根产品ID*/
    	$topid = D('Category')->getTopId($allcart[0]["id"]);
    	$title=$topid["title"];
    	$topid = $topid['id'];
    	$price=0;
    	for ($i=0; $i<count($allcart); $i++){
    		$theArray = $Document->detail($allcart[$i]["id"]);
    		$theArray["Mark"]=$allcart[$i]["Mark"];
    		$price=$price+$theArray["price"];
    		$NewsList[] = $theArray;
    	}
    	$this->assign("NewsList",$NewsList);
    	$this->assign("topid",$topid);
    	$this->assign("title",$title);
    	$this->assign("price",$price);
    	$this->display();
    }
    //添加套餐cookie
	public function UpdateCookie($id=0){
		if ($id!=0) {
			$allcart = json_decode(cookie('package'),true);
			$temp["id"]=$id;
			$temp["Mark"]=count($allcart)+1;
			$allcart[]=$temp;
			$allcart =json_encode($allcart);
			cookie('package',$allcart);
			$this->redirect('add');
		}
	}
	
}
