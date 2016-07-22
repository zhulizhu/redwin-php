<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ShoppingController extends HomeController {
	
	

	//系统首页
    public function index(){
    	
        $category = D('Category')->getTree();
        $lists    = D('Document')->lists(null);

        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页
        $this->display();
    }
    /**
     * 修改其他奖品数量
     * @param number $id
     * @param number $length
     */
    public function UpdataLength($id=0,$length=null){
    	$product = M('Product')->where('id='.$id)->find();
    	$allcart = json_decode(cookie('cart'),true);
    	$Quantity=0;
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);
    	for($i=0; $i<count($theObj); $i++){
    		if ($theObj[$i]["id"]==$id) {
    			if ($length=="Minus") {
    				if($theObj[$i]["length"]>1){
    					$theObj[$i]["length"]=($theObj[$i]["length"]-1);
    				}
    				$Quantity=$theObj[$i]["length"];
    			}else{
    				$rest = $product['total']-$product['join'];
    				
    				if($theObj[$i]["length"]<$rest){
    					$theObj[$i]["length"]=($theObj[$i]["length"]+1);
    				}else{
    					$theObj[$i]["length"] = $rest;
    				}
    				$Quantity=$theObj[$i]["length"];
    			}
    			
    		}
    	}
    	$allcart["obj"]=$theObj;
    	cookie('cart',json_encode($allcart));
    	$this->redirect('cart');
    }
    
    /**
     * 直接修改购物车数据
     * @param number $id
     * @param number $length
     */
    public function UpdataCarts($id=0,$length){
    	$allcart = json_decode(cookie('cart'),true);
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);
    	for($i=0; $i<count($theObj); $i++){
    		if ($theObj[$i]["id"]==$id) {
    			$theObj[$i]["length"]=$length;
    		}
    	}
    	$allcart["obj"]=$theObj;
    	cookie('cart',json_encode($allcart));
    	echo $id;
    	exit();
    }
    /**
     * 修改整车奖品数量
     * @param number $id
     * @param number $length
     */
    public function UpdataLengths($id=0,$length=null){
    	$product = M('Product')->where('id='.$id)->find();
    	$allcart = json_decode(cookie('cart'),true);
    	//$Quantity=0;
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);
    	for($i=0; $i<count($theObj); $i++){
    		if ($theObj[$i]["id"]==$id) {
    			if ($length=="Minus") {
    				if($theObj[$i]["length"]>10){
    					$theObj[$i]["length"]=($theObj[$i]["length"]-10);
    				}
    				//$Quantity=$theObj[$i]["length"];
    			}else{
    				$rest = $product['total']-$product['join'];
    				
    				if($theObj[$i]["length"]<$rest){
    					$theObj[$i]["length"]=($theObj[$i]["length"]+10);
    				}else{
    					$theObj[$i]["length"] = $rest;
    				}
    				
    				//$Quantity=$theObj[$i]["length"];
    			}
    			 
    		}
    	}
    	$allcart["obj"]=$theObj;
    	cookie('cart',json_encode($allcart));
    	$this->redirect('cart');
    }
    
    /**
     * 清空购物车
     */
    public function Emptys(){
    	$allcart = json_decode(cookie('cart'),true);
    	$allcart=null;
    	cookie('cart',json_encode($allcart));
    	$this->redirect('cart');
    }
    /**
     * 删除商品
     * @param number $id产品ID
     */
    public function Delete($id=0){
    	$allcart = json_decode(cookie('cart'),true);
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);
    	for ($i=0; $i<count($theObj); $i++){
    		if ($theObj[$i]["id"]==$id) {
    			unset($theObj[$i]);
    		}
    	}
    	$allcart["obj"]=$theObj;
    	cookie('cart',json_encode($allcart));
    	$this->redirect('cart');
    }
    /**
     * 购物车
     */
    public function cart($tpl=0){
    	$allcart = json_decode(cookie('cart'),true);
    	$Product = D('Product');
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);

    	for($i=0;$i<count($theObj);$i++){
    		$theArray = $Product->detail($theObj[$i]["id"]);
    		$theArray['length'] = $theObj[$i]["length"];
    		$theArray['prices']=$theObj[$i]["length"]*$theArray["price"];
    		$NewsList[] = $theArray;
    	}
    	//底部推荐奖品
    	$reco=$this->getOrderList();
    	$this->assign('reco',$reco);//推荐奖品
    	$this->assign('NewsList', $NewsList);
    	$this->display('Shopping/Cart');
    }
    
    
    //结算
    public function Settlement(){
    	if ( !is_login() ) {
    		setcookie("href", "shopping/settlement", time()+3600);
    		$this->error( '您还没有登陆!',U('User/login') );
    	}
    	$allcart = json_decode(cookie('cart'),true);
    	$Product = D('Product');
    	$theObj = $allcart["obj"];
    	$theObj=array_values($theObj);
    	$Money=0;
    	$prod_length=0;
    	for($i=0;$i<count($theObj);$i++){
    		$theArray = $Product->detail($theObj[$i]["id"]);
    		$theArray['length'] = $theObj[$i]["length"];
    		$theArray['prices']=$theObj[$i]["length"]*$theArray["price"];
    		$Money=$Money+$theArray['prices']*9.9;
    		$prod_length = $prod_length+ $theArray['length'];
    		$NewsList[] = $theArray;
    	}
    	//底部推荐奖品
    	$reco=$this->getOrderList();
    	$this->assign('reco',$reco);//推荐奖品
    	$this->assign("money",$Money);//总价格
    	$this->assign("prod_length",$prod_length);//总数量
    	$this->assign('NewsList', $NewsList);
    	$this->display();
    }
    
    /**
     * 加入和修改购物车函数
     * @param number $id
     * @param number $length
     */
 public  function  UpdateCart($id=0,$length=1){
    	$length = $_POST['shopnum'];
    	if($length<0||$length==0){$this->error('奖品数量有误！');}
    	if($id!=0){
    		$allcart = json_decode(cookie('cart'),true);
    		if ($allcart==null) {
    			$temp['id'] = $id;
    			$temp['length'] = $length;
    			$allcart['length'] = $length;
    			$allcart['obj'][]=$temp;
    			$allcart =json_encode($allcart);
    			cookie('cart',$allcart);
    			$this->redirect('cart');
    		}else{
    			$ex=true;
    			$theObj = $allcart["obj"];
    			$theObj=array_values($theObj);
    			for ($i=0; $i<count($theObj); $i++){
    				if ($theObj[$i]["id"]==$id) {
    					$product = M('Product')->where('id='.$id)->find();
    					$rest = $product['total']-$product['join'];
    					$theObj[$i]["length"]=($theObj[$i]["length"] + $length);
    					if($theObj[$i]["length"]>$rest){$theObj[$i]["length"] = $rest;}
    					$allcart["length"] = count($theObj);
    					$ex=false;
    				}
    			}
    			if ($ex) {
    				$temp['id'] = $id;
    				$temp['length'] = $length;
    				$allcart["length"] = count($theObj);
    				$theObj[]=$temp;
    			}
    			$allcart["obj"] = $theObj;
    			cookie('cart',json_encode($allcart));
    			$this->redirect('cart');
    		}
    	}else{
    		$this->error("加入购物车失败");
    	}
    }
	/**
     * 提交成功支付状态
     * 
     */
    public function Payment(){		
		
    }
    /**
     * 支付页面
     */
    public function alipay(){
    	$id = $_GET['id'];
    	$order = M('Order')->where('id='.$id)->find();
    	$this->assign('order',$order);
    	$this->display();
    }
    /**
     * 刷新商品数量
     */
    public function pro_flash(){
    	$id = $_POST['pro_id'];
    	if($id){
    		$pro_info = M('Product')->where("id=".$id)->find();
    		$rest = $pro_info['total'] - $pro_info['join'];
    		echo $rest;
    		exit();
    	}
    	exit;
    }
   
    
    
}