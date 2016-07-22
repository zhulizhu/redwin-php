<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
	 

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->display('other/404');
		//$this->redirect('Index/404');
	}


    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        //品牌
        //$this->assign ( 'brands', $this->brands());
        /*用户ID*/
        define('HUID',is_login());
		
        
    }
    /**
     * 购物车列表
     */
    public function dingdan(){
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
    		$Money=$Money+$theArray['prices'];
    		$prod_length = $prod_length+ $theArray['length'];
    		$NewsList[] = $theArray;
    	}
    	$this->assign ('dingdans', $NewsList );
    	$this->assign("Total",$Money);//总价格
    	$this->assign("number",$prod_length);//总数量
    	$this->display ("taglib/cart/CartList");
    }
    
    /**
     *品牌
     */
    public function brands(){
    	$where["pid"]=2974 ;
    	$list = M ( "linkage" )->where ($where)-> select ();
    	for($i=0; $i<count($list); $i++){
    		$list[$i]["image"]=picture($list[$i]["picture"]);
    	}
    	return $list;
    }
    /**
     * 广告方法
     * @param number $pid
     * @param string $tpl
     */
    public function ad($pid=0,$tpl=""){
    	$where['status'] = 1;
		$where['pid'] = $pid;
		$sort = M('AdsSort')->where('id='.$pid)->find();
		$list = M("Ads")->where($where)->select();
		$this->assign('AdSort',$sort);
		$this->assign('Adlist',$list);
		$this->display("taglib/Slide/".$tpl);
    }
    
    /**
     * 当前位置
     * @param unknown $id
     * @param unknown $type
     */
    public function NowAddress(){
    	$Category = D("Category");
    	//自动设置类型
    	$cate = $_REQUEST;
    	
    	if(isset($cate['category'])){
    		$info = $Category->info($cate['category']);
    	}elseif (isset($cate['category']) && isset($cate['id'])){
    		$info = $Category->info($cate['category']);
    	}else{
    		$info = D('Document')->detail($cate['id']);
    		$info = $Category->info($info['category_id']);
    	}
    	
    	if(!$info){
    		$this->error('很抱歉，系统发生错误。');
    	}

    	//根据类型判断格式
    	$topcate = $Category->getTopId($info['id']);
    	$theArray = array();
    	switch ($topcate['lefttype']){
    		case 0://新闻列表
    			if($info['pid']==0){
    				$theArray[] = array('title'=>$info['title'],'url'=>U('Article/index?category='.$info['name']));
    			}else{
    				$result = $Category->getTopDesc($info['id']);
    				$theArray = $this->AutoUrl($result);
    			}
    		break;
    		case 1:
    			$theArray[] = array('title'=>$info['title'],'url'=>U('Article/intro?category='.$info['name']));
    			if(isset($cate['category']) && isset($cate['id'])){
    				$detail = M('document')->where('id='.$cate['id'])->find();
    				$theArray[] = array('title'=>$detail['title'],'url'=>'');
    			}
    		break;
    	}
    	$this->assign('NowAddress',$theArray);
    }
    /**
     * 
     * 
     * @author 智网天下科技 http://www.cheewo.com
     */
    public function ProductAddress(){
    	$Procate = D("Procate");
    	//自动设置类型
    	$cate = $_REQUEST;
    	if(isset($cate['category'])){
    		$info = $Procate->info($cate['category']);
    	}elseif (isset($cate['category']) && isset($cate['id'])){
    		$info = $Procate->info($cate['category']);
    	}else{
    		$info = D('Product')->detail($cate['id']);
    		$info = $Procate->info($info['category_id']);
    	}
    	if(!$info){
    		$this->error('很抱歉，系统发生错误。');
    	}
    	//根据类型判断格式
    	$topcate = $Procate->getTopId($info['id']);
    	$theArray = array();
    	switch ($topcate['lefttype']){
    		case 0://新闻列表
    			if($info['pid']==0){
    				$theArray[] = array('title'=>$info['title'],'url'=>U('Product/index?category='.$info['name']));
    			}else{
    				$result = $Procate->getTopDesc($info['id']);
    				$theArray = $this->AutoUrl($result);
    			}
    			break;
    		case 1:
    			$theArray[] = array('title'=>$info['title'],'url'=>U('Product/intro?category='.$info['name']));
    			if(isset($cate['category']) && isset($cate['id'])){
    				$detail = M('document')->where('id='.$cate['id'])->find();
    				$theArray[] = array('title'=>$detail['title'],'url'=>'');
    			}
    			break;
    	}
    	$this->assign('NowAddress',$theArray);
    }
    /**
     * 自动填充URL
     * @param unknown $theArray
     * @return multitype:multitype:NULL Ambigous <string, unknown>
     */
    public function AutoUrl($theArray){
    	$newArray = array();
    	for($i=count($theArray)-1;$i>=0;$i--){
    		if($theArray[$i]['pid']==0){
    			$url = U('Article/index?category='.$theArray[$i]['name']);
    		}else{
    			$url = U('Article/lists?category='.$theArray[$i]['name']);
    		}
    		$newArray[] = array('title'=>$theArray[$i]['title'],'url'=>$url);
    	}
    	return $newArray;
    }
    
/**
	 * 新闻左侧
	 * 
	 * @param unknown $theArray        	
	 * @param string $tpl        	
	 */
	public function LeftNav($theArray, $tpl = "left") {
		$model = D ( 'Category' );
		if (! empty ( $theArray ['category'] )) { // 通过栏目标识来查找
			$info = $model->info ( $theArray ['category'] );
			$Document = D ( 'Document' );
			switch ($info ['lefttype']) {
				case 0 : // 新闻列表
					if ($info ['pid'] == 0 || $info ['pid'] == 65) {
						$tree = $model->getSortUrl ( $model->getTree ( $info ['id'] ) );
						$thisleft = $info;
					} else {
						$tree = $model->getSortUrl ( $model->getSameLevel ( $info ['id'] ) );
						$thisleft = $model->info ( $info ['pid'] );
					}
					break;
				case 1 : // 一级单页
					$tree = $model->getIntroUrl ( $Document->lists ( $info ['id'] ) );
					$thisleft = $info;
					break;
				case 2 : // 单页
					$tree = $model->getIntroUrl ( $Document->lists ( $info ['id'] ) );
					$thisleft = $info;
					break;
			}
		} else if (! empty ( $theArray ['id'] )) {
			$Document = D ( 'Document' );
			$info = $Document->detail ( $theArray ['id'] );
			$info = $model->info ( $info ['category_id'] );
			switch ($info ['lefttype']) {
				case 0 : // 新闻列表
					if ($info ['pid'] == 0) {
						$tree = $model->getSortUrl ( $model->getTree ( $info ['id'] ) );
						$thisleft = $info;
					} else {
						$tree = $model->getSortUrl ( $model->getSameLevel ( $info ['id'] ) );
						$thisleft = $model->getTopId ( $info ['id'] );
					}
					break;
				case 1 : // 一级单页
					$tree = $model->getIntroUrl ( $Document->lists ( $info ['id'] ) );
					$thisleft = $info;
					break;
				case 2 : // 单页
					$tree = $model->getIntroUrl ( $Document->lists ( $info ['id'] ) );
					$thisleft = $tree;
					break;
			}
		} else {
			$this->error ( "未传参数" );
		}
		$this->assign ( 'thisleft', $thisleft );
		$this->assign("info",$info);
		$this->assign ( 'leftnav', $tree );
		$this->display ( 'taglib/leftnav/' . $tpl );
	}
   /*产品左侧*/
   public function Product_LeftNav($theArray, $tpl = "") {
	   	/*获取根ID*/
		$pid=$this->productSort($theArray["id"]);
		/*获取根类名称*/
		$Procate = D ( 'Procate' );
		$SortName=$Procate->info($pid);
		/*获取递归数组*/
		$PrductList=$this->Recursion($pid);
		$HtmlUL=$this->TraversalUL($PrductList,$theArray);
		$this->assign ( 'HtmlUL', $HtmlUL );
		$this->assign ( 'SortName', $SortName );
		$this->display ( 'taglib/leftnav/Productleft');
   }
   /**
   	 *递归循环遍历数字成li标签
	 *@PrductList unknown 递归数组
	 *@theArray unknown 当前所属id
	 *@return unknown 遍历好的li
	 */
	 public function TraversalUL($PrductList,$theArray){
	 	$value = "";
		if(count($PrductList)>0){
			$value .= "<ul>\r\n";
			foreach ( $PrductList as $row ) {
				if($theArray["pid"] == $row["id"] || $theArray["id"] == $row["id"] ){
					$value .="<li class=\"onfocus\">\r\n";
				}else{
					$value .="<li>\r\n";
				}
				if(count($row["list"])>0){
					$value .= "<em><a href=\"".getprocateu($row["id"])."\">".$row["title"]."</a></em>";
				}else{
					$value .= "<a href=\"".getprocateu($row["id"])."\">".$row["title"]."</a>";	
				}
				if(count ( $row ['list'] ) > 0){
					$value .=$this->TraversalUL($row["list"],$theArray);
				}
				$value .="</li>\r\n";
			}
			$value .= "</ul>\r\n";
		}
		return $value;
	 }
	/**
   	 * 递归查询根ID
   	 * @id unknown 任意ID
   	 * @return unknown 返回根ID
   	 */
    public function productSort($id){ 
    	$M = M("Procate");
    	$list=$M->where(array("id"=>$id))->field("id,pid")->find();
		$pid=0;
		if(count($list)>0){
			if($list["pid"]!=0){
				 $pid=$this->productSort($list["pid"]);
			}else
			{
				$pid=$list["id"];
			}
		}
		return $pid;
    }
  /**
   * 递归循环遍历
   * @id unknown 根id
   * @return unknown 返回递归数组
   */
   public function Recursion($id){
	   $M = M("Procate");
	   $list=$M->where(array("pid"=>$id))->field("id,pid,title,name")->select();
	   $theArray=array();
	   if (count($list)>0) {
		  for ($i=0;$i<count($list);$i++){
			  $list[$i]["list"]=self::Recursion($list[$i]['id']);
		  }
	   }
	   $theArray=$list;
	   return $theArray;
   }
	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}
	
	/**
	 * 通用分页列表数据集获取方法
	 *
	 *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
	 *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
	 *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
	 *
	 * @param sting|Model  $model   模型名或模型实例
	 * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
	 * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
	 *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
	 *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
	 *
	 * @param array        $base    基本的查询条件
	 * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
	 * @author 朱亚杰 <xcoolcc@gmail.com>
	 *
	 * @return array|false
	 * 返回数据集
	 */
	protected function lists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
		$options    =   array();
		$REQUEST    =   (array)I('request.');
		if(is_string($model)){
			$model  =   M($model);
		}
	
		$OPT        =   new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);
	
		$pk         =   $model->getPk();
		if($order===null){
			//order置空
		}else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
			$options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
		}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
			$options['order'] = $pk.' desc';
		}elseif($order){
			$options['order'] = $order;
		}
		unset($REQUEST['_order'],$REQUEST['_field']);
	
		$options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
			if($val===''||$val===null){
				return false;
			}else{
				return true;
			}
		});
		if( empty($options['where'])){
			unset($options['where']);
		}
		$options      =   array_merge( (array)$OPT->getValue($model), $options );
		$total        =   $model->where($options['where'])->count();
	
		if( isset($REQUEST['r']) ){
			$listRows = (int)$REQUEST['r'];
		}else{
			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
		}
		$page = new \Think\Page($total, $listRows, $REQUEST);
		if($total>$listRows){
			$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		}
		$p =$page->show();
		$this->assign('_page', $p? $p: '');
		$this->assign('_total',$total);
		$options['limit'] = $page->firstRow.','.$page->listRows;
	
		$model->setProperty('options',$options);
	
		return $model->field($field)->select();
	}
	
	/**
	 *获取当前用户所在的登陆地址
	 */
	function getIPLoc_QQ($queryIP){
		$url = 'http://ip.qq.com/cgi-bin/searchip?searchip1='.$queryIP;
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_ENCODING ,'gb2312');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
		$result = curl_exec($ch);
		$result = mb_convert_encoding($result, "utf-8", "gb2312"); // 编码转换，否则乱码
		curl_close($ch);
		preg_match("@<span>(.*)</span></p>@iU",$result,$ipArray);
		dump(curl_setopt);
		$loc = $ipArray[1];
		return $loc;
	}
	/**
	 *获取按所需剩余人数升序排列的产品列表
	 */
	function getOrderList($list){
		if($list==null){
			$list = D('product')->lists();
		}
		for ($i=0;$i<count($list); $i++){
			$list[$i]['diff']=$list[$i]['total']-$list[$i]['join'];
		} 
		$arrSort = array();  
		foreach($list AS $uniqid => $row){  
    		foreach($row AS $key=>$value){  
        		$arrSort[$key][$uniqid] = $value;  
    		}  
		}  
    	array_multisort($arrSort['diff'], constant('SORT_ASC'), $list); 
    	 
		return $list;
	}
}
