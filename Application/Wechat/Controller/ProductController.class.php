<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Wechat\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ProductController extends HomeController {
	
	/* 文档模型频道页 */
	public function index() {

		$where = array();
		$where['category_id'] = 332;
		$where['status'] = 1;
		$list = M("Product")->where($where)->select();
		$this->assign("prolist",$list);
		$this->assign("sharetitle","产品中心-");

		$where = array();
		$where['model_id'] = 10;
		$where['type'] = "linkage";
		$extra = M("attribute")->where($where)->getField("extra",true);
		$newArray = array();
		for($i=0;$i<count($extra);$i++){
			$temp = array();
			$temp['name'] = $extra[$i];
			$where = array();
			$where['name'] = $extra[$i];
			$where['pid'] = array("neq",0);
			$list = M("linkage")->where($where)->select();
			$temp['list'] = $list;
			$newArray[] = $temp;
		}
		$this->assign("newArray",$newArray);
		$this->display ();
	}
	
	/* 文档模型列表页 */
	public function lists($p = 1) {
		/* 分类信息 */
		$category = $this->category ();
		// 导航信息
		// $this->ProductAddress($category['id']);
		/* 获取当前分类列表 */
		$Product = D ( 'Product' );
		$lenght = $Product->lists ( $category ['id'] );
		$list = $Product->lists ( $category ['id'] );
		
		if (false === $list) {
			$this->error ( '获取列表数据失败！' );
		}
		if (empty ( $category ['template_index'] )) {
			$tpl = 'Product/lists';
		} else {
			$tpl = $category ['template_index'];
		}
		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'Pro_list', $list );
		$this->assign ( 'Lengths', count ( $lenght ) );
		$this->assign ( "info", array (
				'title' => $category ['title'] 
		) );
		$this->assign("sharetitle",$category['title']."-产品列表-");
		$this->display ( $tpl );
	}
	/* 筛选查询 */
	public function Screening($p = 1) {
		/* 分类信息 */
		$category = $this->category ();
		// 导航信息
		// $this->ProductAddress($category['id']);
		/* 获取当前分类列表 */
		$where = I ( 'get.' );
		if (isset ( $where ['category'] )) {
			$get_category = $where ['category'];
		}
		unset ( $where ["category"] );
		$data = M ( 'ProductProlist' )->where ( $where )->getField ( 'id', true );
		$lenght = count ( $data );
		$data = implode ( ",", $data );
		$Product = D ( 'Product' );
		$list = $Product->page ( $p, $category ['list_row'] )->Screening ( $category ['id'], $data );
		/* 获取长度 */
		$lenght = $Product->Screening ( $category ['id'], $data );
		if (false === $list) {
			$this->error ( '获取列表数据失败！' );
		}
		if (empty ( $category ['template_index'] )) {
			$tpl = 'Product/Screening';
		} else {
			$tpl = $category ['template_index'];
		}
		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'News_list', $list );
		$this->assign ( 'Lengths', count ( $lenght ) );
		if (isset ( $where ['pinpai'] )) {
			$info = D ( 'Home/Linkage' )->info ( $where ['pinpai'] );
		}
		$this->assign ( "info", $info ); // 设置标题
		$this->display ( $tpl );
	}
	public function Brand($p = 1) {
		$this->display ( "Article/Brand/index" );
	}
	/* 文档模型列表页 */
	public function intro($id = 0, $p = 1) {
		if (! is_login ()) {
			$this->redirect ( 'User/login' );
		}
		/* 分类信息 */
		$category = $this->category ();
		// 导航信息
		$this->NowAddress ( $category ['id'] );
		
		/* 获取当前分类列表 */
		$Document = D ( 'Document' );
		$map ['status'] = 1;
		$map ['category_id'] = $category ['id'];
		if ($id != 0) {
			$map ['id'] = $id;
		}
		$info = $Document->where ( $map )->find ();
		$info = $Document->detail ( $info ['id'] );
		if (false === $info) {
			$this->error ( '获取数据失败！' );
		}
		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'info', $info );
		$this->display ( $category ['template_lists'] );
	}
	public function get_content($id) {
		$info = D ( 'Product' )->detail ( $id );
		echo "产品参数<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		exit ();
	}
	public function get_shuxing($id) {
		$info = D ( 'Product' )->detail ( $id );
		echo $info ['content'];
		exit ();
	}
	public function announced($id = 0, $p = 1) {
		if (! $id && is_numeric ( ($id) )) {
			$this->error ( '没有id传入' );
		}
		$p = intval ( $p );
		$p = empty ( $p ) ? 1 : $p;
		$Document = D ( 'Product' );
		$info = $Document->detail ( $id );
		if (! info) {
			$this->error ( $Document->getError () );
		}
		
		/* 更新浏览数 */
		$map = array (
				'id' => $id 
		);
		$Document->where ( $map )->setInc ( 'view' );
		/* 获取系列图片 */
		if ($info ["picture"] != "") {
			$where ["id"] [] = "in";
			$where ["id"] [] = $info ["picture"];
			$result = M ( 'picture' )->where ( $where )->select ();
			for($i = 0; $i < count ( $result ); $i ++) {
				$result [$i] ["photo"] = picture ( $result [$i] ["id"] );
			}
			$this->assign ( 'picture', $result );
		}
		
		/* 添加浏览记录 */
		if (is_login ()) {
			$viewmap ['uid'] = is_login ();
			$viewmap ['proid'] = $info ['id'];
			$viewinfo = M ( 'view' )->where ( $viewmap )->getfield ( "id" );
			if (! $viewinfo) {
				$viewmap ['create_time'] = NOW_TIME;
				$viewmap ['update_time'] = NOW_TIME;
				M ( 'view' )->add ( $viewmap ); // 添加浏览
			} else {
				$viewupdate ['update_time'] = NOW_TIME;
				M ( 'view' )->where ( 'id=' . $viewinfo )->save ( $viewupdate ); // 更新最后浏览时间
			}
		}
		
		// 评论数
		$plcount = M ( 'pingjia' )->where ( 'pro_id=' . $info ['id'] )->count ( "id" );
		$info ['plcount'] = $plcount;
		// 收藏
		$cmap ['uid'] = is_login ();
		$cmap ['proid'] = $info ['id'];
		$likeid = M ( 'Like' )->where ( $cmap )->getfield ( 'id' );
		if (! $likeid) {
			$likeid = "收藏";
		} else {
			$likeid = "已收藏";
		}
		
		$ordlist = M ( 'Orderlist' )->where ( 'pro_id=' . $info ['id'] )->order ( 'id desc' )->select ();
		$length = count ( $ordlist );
		for($i = 0; $i < $length; $i ++) {
			$ord = M ( 'Order' )->where ( 'id=' . $ordlist [$i] ['order_id'] )->find ();
			if ($ord ['status'] != 2) {
				
				unset ( $ordlist [$i] );
			} else {
				$ordlist [$i] ['create_time'] = $ord ['create_time'];
				$ordlist [$i] ['nickname'] = get_nickname ( $ordlist [$i] ['uid'] );
				$ordlist [$i] ['photo'] = M ( 'Member' )->where ( 'uid=' . $ordlist [$i] ['uid'] )->getField ( 'picture' );
			}
		}
		$this->assign ( 'ordlist', $ordlist );
		$this->assign ( "like", $likeid );
		/* 模板赋值并渲染模板 */
		$this->assign ( 'info', $info );
		$this->assign ( 'page', $p ); // 页码
		$this->display ();
	}
	
	/* 文档模型详情页 */
	public function detail($id = 0, $p = 1) {
		/* 标识正确性检测 */
		if (! ($id && is_numeric ( $id ))) {
			$this->error ( '产品ID错误！' );
		}
		
		/* 页码检测 */
		$p = intval ( $p );
		$p = empty ( $p ) ? 1 : $p;
		
		/* 获取详细信息 */
		$Product = D ( 'Product' );
		$info = $Product->detail ( $id );
		if (! $info) {
			$this->error ( $Product->getError () );
		}
		/* 分类信息 */
		$category = $this->category ( $info ['category_id'] );
		
		// 导航信息
		//$this->NowAddress ( $id, "detail" );
		
		/* 获取模板 */
		if (! empty ( $info ['template'] )) { // 已定制模板
			$tmpl = $info ['template'];
		} elseif (! empty ( $category ['template_detail'] )) { // 分类已定制模板
			$tmpl = $category ['template_detail'];
		} else { // 使用默认模板
			$tmpl = 'Product/detail';
		}
		
		if($info['category_id']==310){
			if (! is_login ()) {
				Cookie ( '__furl__',U('detail?id='.$id));
				$this->redirect("User/login");
				exit;
			}
			$tmpl = "Product/jifen_detail";
		}
		
		
		/* 更新浏览数 */
		$map = array (
				'id' => $id 
		);
		$Product->where ( $map )->setInc ( 'view' );
		/* 获取系列图片 */
		if ($info ["picture"] != "") {
			$where ["id"] [] = "in";
			$where ["id"] [] = $info ["picture"];
			$result = M ( 'picture' )->where ( $where )->select ();
			for($i = 0; $i < count ( $result ); $i ++) {
				$result [$i] ["photo"] = thumb ( $result [$i] ["id"] , 700 ,700 );
			}
			$this->assign ( 'picture', $result );
		}
		
		/* 查询地区 */
		// $this->Coordinates();
		
		/* 添加浏览记录 */
		if (is_login ()) {
			$viewmap ['uid'] = is_login ();
			$viewmap ['proid'] = $info ['id'];
			$viewinfo = M ( 'view' )->where ( $viewmap )->getfield ( "id" );
			if (! $viewinfo) {
				$viewmap ['create_time'] = NOW_TIME;
				$viewmap ['update_time'] = NOW_TIME;
				M ( 'view' )->add ( $viewmap ); // 添加浏览
			} else {
				$viewupdate ['update_time'] = NOW_TIME;
				M ( 'view' )->where ( 'id=' . $viewinfo )->save ( $viewupdate ); // 更新最后浏览时间
			}
		}
		
		// 评论数
		$pl_where['pro_id'] = $id;
		$pl_where['status'] = 1;
		$pjlist = M('prints')->where($pl_where)->order("id desc")->select();
		$this->assign("pjlist",$pjlist);
		// 收藏
		$cmap ['uid'] = is_login ();
		$cmap ['proid'] = $info ['id'];
		$likeid = M ( 'Like' )->where ( $cmap )->getfield ( 'id' );
		if (! $likeid) {
			$likeid = "收藏";
		} else {
			$likeid = "已收藏";
		}
		$this->assign ( "like", $likeid );
		
		/* 自适应价格，仅此系统用到，复制调试时请删除 */
		/* 购物车信息 */
		$cartcount = D('cart')->get_count();
		$this->assign("cartcount",$cartcount);

		//限时秒杀
		$where = array();
		$where['start_time'] = array("lt",NOW_TIME);
		$where['end_time'] = array("gt",NOW_TIME);
		$killid = M("seckill")->where($where)->getField("id");
		if($killid){
			$where = array();
			$where['pid'] = $killid;
			$where['proid'] = $id;
			$killinfo = M("seckill_list")->where($where)->find();
			if($killinfo){
				$where = array();
				$where['id'] = $killid;
				$kill_end_time = M("seckill")->where($where)->getField("end_time");
				$killinfo['end_time'] = $kill_end_time;
				$this->assign("killinfo",$killinfo);
			}
		}

		/* 模板赋值并渲染模板 */
		$this->assign ( 'category', $category );
		$this->assign ( 'info', $info );
		$this->assign ( 'page', $p ); // 页码
		$this->assign("sharetitle",$info['title']."-产品详情-");
		$this->assign("shareimg","http://".C('WEB_SITE_DOMAIN').picture($info['cover_id']));
		$this->display ( $tmpl );
	}
	
	
	/* 搜索页面 */
	public function Search($p = 1) {
		$Product = D ( 'Product' );
		$where['description'] = array("like","%".I('get.txt')."%");
		$where['status'] = 1;
		$where['display'] = 1;
		$list = M('Product')->where($where)->select();
		$this->assign ( 'Pro_list', $list );
		$this->assign ("title","搜索结果");
		$this->assign("sharetitle",I('get.key')."-搜索结果-");
		$this->display ( "Product/lists" );
	}
	/* 搜索品牌产品 */
	public function Searchs($p = 1) {
		$where = $_GET;
		$Product = D ( 'Product' );
		$list_length = $Product->Fuzzys ( $where, '`level` DESC,`id` DESC', 1, true, true );
		$list = $Product->page ( $p, 52 )->Fuzzys ( $where, '`level` DESC,`id` DESC', 1, true, true );
		$this->assign ( 'Pro_list', $list );
		$this->assign ( 'Lengths', count ( $list_length ) );
		$this->display ( "Product/lists" );
	}
	public function allList() {
		$map ['id'] = array (
				'in',
				array (
						281,
						282,
						283,
						284 
				) 
		);
		$list = M ( 'Procate' )->where ( $map )->select ();
		$this->assign ( 'list', $list );
		$this->display ();
	}
	
	/**
	 * 当前位置
	 * 
	 * @param unknown $id        	
	 * @param unknown $type        	
	 */
	public function NowAddress() {
		$Category = D ( "Procate" );
		// 自动设置类型
		$cate = $_REQUEST;
		if (isset ( $cate ['category'] )) {
			$info = $Category->info ( $cate ['category'] );
		} elseif (isset ( $cate ['category'] ) && isset ( $cate ['id'] )) {
			$info = $Category->info ( $cate ['category'] );
		} else {
			$info = D ( 'Product' )->detail ( $cate ['id'] );
			$info = $Category->info ( $info ['category_id'] );
		}
		
		if (! $info) {
			$this->error ( '很抱歉，系统发生错误。' );
		}
		
		// 根据类型判断格式
		$topcate = $Category->getTopId ( $info ['id'] );
		$theArray = array ();
		switch ($topcate ['lefttype']) {
			case 0 : // 新闻列表
				if ($info ['pid'] == 0) {
					$theArray [] = array (
							'title' => $info ['title'],
							'url' => U ( 'Product/index?category=' . $info ['name'] ) 
					);
				} else {
					$result = $Category->getTopDesc ( $info ['id'] );
					$theArray = $this->AutoUrl ( $result );
				}
				break;
			case 1 :
				$theArray [] = array (
						'title' => $info ['title'],
						'url' => U ( 'Product/intro?category=' . $info ['name'] ) 
				);
				if (isset ( $cate ['category'] ) && isset ( $cate ['id'] )) {
					$detail = M ( 'Product' )->where ( 'id=' . $cate ['id'] )->find ();
					$theArray [] = array (
							'title' => $detail ['title'],
							'url' => '' 
					);
				}
				break;
		}
		$this->assign ( 'NowAddress', $theArray );
	}
	
	/**
	 * 自动填充URL
	 * 
	 * @param unknown $theArray        	
	 * @return multitype:multitype:NULL Ambigous <string, unknown>
	 */
	public function AutoUrl($theArray) {
		$newArray = array ();
		for($i = count ( $theArray ) - 1; $i >= 0; $i --) {
			if ($theArray [$i] ['pid'] == 0) {
				$url = U ( 'Product/index?category=' . $theArray [$i] ['name'] );
			} else {
				$url = U ( 'Product/lists?category=' . $theArray [$i] ['name'] );
			}
			$newArray [] = array (
					'title' => $theArray [$i] ['title'],
					'url' => $url 
			);
		}
		return $newArray;
	}
	/* 查询地区 */
	public function Coordinates() {
		// 获取当前IP位置 外网ip$_SERVER["REMOTE_ADDR"]
		$content = file_get_contents ( "http://ip.taobao.com/service/getIpInfo.php?ip=211.149.185.31" );
		$tem = json_decode ( $content, true );
		// 查询省下面的市
		$where ["title"] = $tem ["data"] ["region"];
		$Province = M ( 'linkage' )->where ( $where )->getField ( 'id', true );
		$Province = implode ( ",", $Province );
		$City = M ( 'linkage' )->where ( array (
				"pid" => $Province 
		) )->select ();
		$this->assign ( 'City', $City );
		// 查询后台授权的区域代理商
		$Regional ['Agents'] = $tem ["data"] ["city"];
		$Delivery = M ( 'brand' )->field ()->where ( $Regional )->select ();
		$this->assign ( 'Delivery', $Delivery );
		
		$Supplier = Count ( $Delivery );
		$this->assign ( 'Supplier', $Supplier );
		
		$this->assign ( 'Coordinate', $tem ["data"] );
	}
	public function Popularvehicle() {
		$this->delOrder ();
		$map ['status'] = 1;
		$map ['state'] = 0;
		$map ['category_id'] = 281;
		$list = M ( 'Product' )->where ( $map )->select ();
		$this->assign ( 'mylist', $list );
		$this->display ();
	}
	public function Automobile() {
		$map ['status'] = 1;
		$map ['state'] = 0;
		$map ['category_id'] = 282;
		$list = M ( 'Product' )->where ( $map )->select ();
		$this->assign ( 'mylist', $list );
		$this->display ();
	}
	public function Beautycare() {
		$this->delOrder ();
		$map ['status'] = 1;
		$map ['state'] = 0;
		$map ['category_id'] = 283;
		$list = M ( 'Product' )->where ( $map )->select ();
		$this->assign ( 'mylist', $list );
		$this->display ();
	}
	public function Modificationarea() {
		$map ['status'] = 1;
		$map ['state'] = 0;
		$map ['category_id'] = 284;
		$list = M ( 'Product' )->where ( $map )->select ();
		$this->assign ( 'mylist', $list );
		$this->display ();
	}
	public function all() {
		$this->delOrder ();
		$text = $_GET ['text_ket'];
		if ($text == "全部奖品" || $text == "最新奖品") {
			$Product = D ( 'Product' );
			$list_length = $Product->lists ( null, 0, '`create_time` DESC' );
		} else {
			if (count ( $_GET ) > 0) {
				$where ['title'] = $text;
			} else {
				$where = I ( 'post.' );
			}
			$Product = D ( 'Product' );
			$list_length = $Product->Fuzzy ( $where, '`level` DESC,`id` DESC', 1, true, true );
		}
		$this->assign ( 'mylist', $list_length );
		$this->display ();
	}
	public function resultPage($id = 0, $p = 1) {
		if (! $id && is_numeric ( ($id) )) {
			$this->error ( '没有id传入' );
		}
		$p = intval ( $p );
		$p = empty ( $p ) ? 1 : $p;
		$Document = D ( 'Product' );
		$info = $Document->detail ( $id );
		if (! info) {
			$this->error ( $Document->getError () );
		}
		$Document = D ( 'Product' );
		$info = $Document->detail ( $id );
		$map ['state'] = array (
				'neq',
				'0' 
		);
		$map ['status'] = array (
				'neq',
				'-1' 
		);
		$map ['category_id'] = array (
				'in',
				'281,282,283,284,285' 
		);
		$product = M ( 'Product' )->where ( $map )->select ();
		for($i = 0; $i < count ( $product ); $i ++) {
			/* 开奖信息 */
			if ($product [$i] ['state'] == 2) {
				$userdata = M ( 'Member' )->where ( 'uid=' . $product [$i] ['awarduser'] )->find ();
				$product [$i] ['photo'] = $userdata ['picture'];
				$product [$i] ['nickname'] = $userdata ['nickname'];
				$where ['pro_id'] = $product [$i] ['id'];
				$where ['uid'] = $product [$i] ['awarduser'];
				$order = M ( 'Orderlist' )->where ( $where )->select ();
				$product [$i] ['join_count'] = count ( $order );
			}
		}
		foreach ( $product as $k => $val ) {
			if ($id == $val ['id']) {
				$data = $val;
			}
		}
		$this->assign ( 'product', $data );
		$this->display ();
	}
	
	/* 最新揭晓 */
	public function newResult() {
		$map ['state'] = array (
				'neq',
				'0' 
		);
		$map ['status'] = array (
				'neq',
				'-1' 
		);
		$map ['category_id'] = array (
				'in',
				'281,282,283,284,285' 
		);
		$product = M ( 'Product' )->where ( $map )->select ();
		for($i = 0; $i < count ( $product ); $i ++) {
			/* 50个时间 */
			$map ['status'] = array (
					'neq',
					- 1 
			);
			$map ['pro_id'] = $product [$i] ['id'];
			$last = M ( 'Orderlist' )->where ( $map )->order ( 'id desc' )->find ();
			$ord = M ( 'Order' )->where ( 'id=' . $last ['order_id'] )->find ();
			$orders = M ( 'Order' )->where ( 'create_time<' . $ord ['create_time'] )->order ( 'id desc' )->limit ( 50 )->select ();
			for($j = 0; $j < 50; $j ++) {
				$product [$i] ['total_time'] += microtime_format ( 'Hmsx', $orders [$j] ['create_time'] );
			}
			/* 开奖信息 */
			if ($product [$i] ['state'] == 2) {
				$userdata = M ( 'Member' )->where ( 'uid=' . $product [$i] ['awarduser'] )->find ();
				$product [$i] ['photo'] = $userdata ['picture'];
				$product [$i] ['nickname'] = $userdata ['nickname'];
				$where ['pro_id'] = $product [$i] ['id'];
				$where ['uid'] = $product [$i] ['awarduser'];
				$order = M ( 'Orderlist' )->where ( $where )->select ();
				$product [$i] ['join_count'] = count ( $order );
			}
		}
		$this->assign ( 'product', $product );
		$this->display ();
	}
	
	/* 获取3d福彩中奖号码 */
	public function getNumber() {
		$srcurl = "http://caipiao.163.com/award/3d/";
		$content = file_get_contents ( $srcurl );
		$start_position = strpos ( $content, '<p id="zj_area">开奖号码： ' );
		$start_position = $start_position + strlen ( '<p id="zj_area">开奖号码： ' );
		$end_position = strpos ( $content, '<span class="tryNum">' );
		$length = $end_position - $start_position;
		$content = substr ( $content, $start_position, $length );
		$content = findNum ( $content );
		echo $content;
		exit ();
	}
	
	/* 开奖 */
	public function UpdatePro() {
		$id = $_POST ['id'];
		$data ['state'] = 2;
		$data ['lottery'] = $_POST ['lottery'] * 1;
		$data ['time_total'] = $_POST ['time_total'] * 1;
		$pro = M ( 'Product' )->where ( 'id=' . $id )->find ();
		$data ['update_time'] = $pro ['update_time'];
		$data ['awardnum'] = ($data ['time_total'] + $data ['lottery']) % $pro ['total'] + 10000001;
		$map ['mynum'] = $data ['awardnum'];
		$map ['pro_id'] = $id;
		$uid = M ( 'Orderlist' )->where ( $map )->getField ( 'uid' );
		$data ['awarduser'] = $uid;
		$res = M ( 'Product' )->where ( 'id=' . $id )->save ( $data );
		echo $res;
		exit ();
	}
	
	/* 购物车 */
	private function Shopping($id = 0) {
	}
	/* 文档分类检测 */
	private function category($id = 0) {
		/* 标识正确性检测 */
		$id = $id ? $id : I ( 'get.category', 0 );
		if (empty ( $id )) {
			$this->error ( '没有指定文档分类！' );
		}
		/* 获取分类信息 */
		$category = D ( 'Procate' )->info ( $id );
		if ($category && 1 == $category ['status']) {
			switch ($category ['display']) {
				case 0 :
					$this->error ( '该分类禁止显示！' );
					break;
				// TODO: 更多分类显示状态判断
				default :
					return $category;
			}
		} else {
			$this->error ( '分类不存在或被禁用！' );
		}
	}
	// 无限极联动
	public function Joint($id) {
		$City = M ( 'linkage' )->where ( array (
				"pid" => $id 
		) )->select ();
		if (count ( $City ) > 0) {
			echo ("[");
			$j = 1;
			for($i = 0; $i < count ( $City ); $i ++) {
				echo ("{id:" . $City [$i] ["id"] . ",title:'" . $City [$i] ["title"] . "'}");
				if ($j != count ( $City )) {
					echo (",");
				}
				$j = $j + 1;
			}
			echo ("]");
		}
	}
	public function joins($id = 0) {
		/* 标识正确性检测 */
		if (! ($id && is_numeric ( $id ))) {
			$this->error ( '文档ID错误！' );
		}
		
		/* 页码检测 */
		$p = intval ( $p );
		$p = empty ( $p ) ? 1 : $p;
		
		/* 获取详细信息 */
		$Document = D ( 'Product' );
		$info = $Document->detail ( $id );
		if (! $info) {
			$this->error ( $Document->getError () );
		}
		
		/* 分类信息 */
		$category = $this->category ( $info ['category_id'] );
		// 导航信息
		$this->NowAddress ( $id, "detail" );
		
		/* 获取模板 */
		if (! empty ( $info ['template'] )) { // 已定制模板
			$tmpl = $info ['template'];
		} elseif (! empty ( $category ['template_detail'] )) { // 分类已定制模板
			$tmpl = $category ['template_detail'];
		} else { // 使用默认模板
			$tmpl = 'Product/detail';
		}
		/* 更新浏览数 */
		$map = array (
				'id' => $id 
		);
		$Document->where ( $map )->setInc ( 'view' );
		/* 获取系列图片 */
		if ($info ["picture"] != "") {
			$where ["id"] [] = "in";
			$where ["id"] [] = $info ["picture"];
			$result = M ( 'picture' )->where ( $where )->select ();
			for($i = 0; $i < count ( $result ); $i ++) {
				$result [$i] ["photo"] = picture ( $result [$i] ["id"] );
			}
			$this->assign ( 'picture', $result );
		}
		/* 查询地区 */
		$this->Coordinates ();
		
		/* 添加浏览记录 */
		if (is_login ()) {
			$viewmap ['uid'] = is_login ();
			$viewmap ['proid'] = $info ['id'];
			$viewinfo = M ( 'view' )->where ( $viewmap )->getfield ( "id" );
			if (! $viewinfo) {
				$viewmap ['create_time'] = NOW_TIME;
				$viewmap ['update_time'] = NOW_TIME;
				M ( 'view' )->add ( $viewmap ); // 添加浏览
			} else {
				$viewupdate ['update_time'] = NOW_TIME;
				M ( 'view' )->where ( 'id=' . $viewinfo )->save ( $viewupdate ); // 更新最后浏览时间
			}
		}
		/* 参与记录 */
		$ordlist = M ( 'Orderlist' )->where ( 'pro_id=' . $info ['id'] )->order ( 'id desc' )->select ();
		$length = count ( $ordlist );
		for($i = 0; $i < $length; $i ++) {
			$ord = M ( 'Order' )->where ( 'id=' . $ordlist [$i] ['order_id'] )->find ();
			if ($ord ['status'] != 2) {
				
				unset ( $ordlist [$i] );
			} else {
				$ordlist [$i] ['create_time'] = $ord ['create_time'];
				$ordlist [$i] ['nickname'] = get_nickname ( $ordlist [$i] ['uid'] );
				$ordlist [$i] ['photo'] = M ( 'Member' )->where ( 'uid=' . $ordlist [$i] ['uid'] )->getField ( 'picture' );
			}
		}
		$this->assign ( 'ordlist', $ordlist );
		// dump($ordlist);
		$this->display ();
	}
	
	// 若用户10分钟未付款则取消订单
	public function delOrder() {
		$map ['payment'] = "微信支付";
		$map ['status'] = "1";
		$order = M ( 'Order' )->where ( $map )->select ();
		foreach ( $order as $k => $v ) {
			if (time () - $v ['create_time'] >= 600) {
				$outtime [] = $v ['id'];
			}
		}
		$my = M ( 'Order' )->where ( array (
				'id' => array (
						'in',
						$outtime 
				) 
		) )->setField ( 'status', - 1 );
	}
	
	// 查询服务商
	public function facilitator($id) {
		$Regional ['Agents'] = $id;
		$Delivery = M ( 'brand' )->field ()->where ( $Regional )->select ();
		if (count ( $Delivery ) > 0) {
			echo ("[");
			$j = 1;
			for($i = 0; $i < count ( $Delivery ); $i ++) {
				echo ("{id:" . $Delivery [$i] ["id"] . ",title:'" . $Delivery [$i] ["title"] . "'}");
				if ($j != count ( $Delivery )) {
					echo (",");
				}
				$j = $j + 1;
			}
			echo ("]");
		}
	}
}