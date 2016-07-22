<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ProductController extends HomeController {

   

	/* 文档模型列表页 */
	public function lists($p = 1){
		/* 分类信息 */
		$category = $this->category();
		//导航信息
		$this->ProductAddress($category['id']);
		/* 获取当前分类列表 */
		$Product = D('Product');
		$list = M("Product")->where("category_id=".$category['id'])->select();
		if(false === $list){
			$this->error('获取列表数据失败！');
		}
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('News_list', $list);
		dump($list);
		exit;
		$this->assign('Lengths', count($list));
		$this->display('Product/lists');
	}
	/*最新揭晓*/
	public function newResult(){
		$map['state'] = array('neq','0');
		$map['status'] = array('neq','-1');
		$map['category_id']=array('in','281,282,283,284,285');
		$product = M('Product')->where($map)->order('update_time desc')->select();
		for($i=0;$i<count($product);$i++){
			/*50个时间*/
			$map1['mynum']= array('neq',0);
			$map1['pro_id']=$product[$i]['id'];
			$last = M('Orderlist')->where($map1)->order('id desc')->find();
			$ord = M('Order')->where('id='.$last['order_id'])->find();
			$map4['create_time'] = array('lt',$ord['create_time']);
			$map4['status'] = 2;
			$orders = M('Order')->where($map4)->order('id desc')->limit(50)->select();
			for($j=0;$j<50;$j++){
				$product[$i]['time_total']+=microtime_format('Hisx',$orders[$j]['create_time']);
			}
			/*开奖信息*/
			if($product[$i]['state']==2){
				$userdata = M('Member')->where('uid='.$product[$i]['awarduser'])->find();
				$product[$i]['photo'] = $userdata['picture'];
				$product[$i]['nickname'] = $userdata['nickname']; 
				$where['pro_id'] = $product[$i]['id'];
				$where['uid'] = $product[$i]['awarduser'];
				$where['mynum'] = array('neq',0);
				$order=M('Orderlist')->where($where)->select();
				$product[$i]['join_count'] = count($order);
			}
			
		}
		$this->assign('sertime',NOW_TIME);
		$this->assign('product',$product);
		//底部推荐奖品
		$reco=$this->getOrderList();
		$this->assign('reco',$reco);//推荐奖品
		$this->display();
	}
	
	/*筛选查询*/
	public function Screening($p = 1){
		$order = $_GET['order'];
		$Product = D('Product');
		$length=$Product->lists();
		if($order=="hot"){
			$list = $Product->page($p,24)->lists(null,0,'`join` DESC');
		}
		if($order=="rest"){
			$list = $Product->page($p,24)->lists(null,0,'`view` DESC');
		}
		if($order=="news"){
			$list = $Product->page($p,24)->lists(null,0,'`create_time` DESC');
		}
		if($order=="totala"){
			$list = $Product->page($p,24)->lists(null,0,'`total` ASC');
		}
		if($order=="totald"){
			$list = $Product->page($p,24)->lists(null,0,'`total` DESC');
		}
		$this->assign('News_list', $list);
		$this->assign('Lengths', count($length));
		$this->display('Product/lists');
	}
	/* 文档模型详情页 */
	public function detail($id = 0, $p = 1){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}

		/* 页码检测 */
		$p = intval($p);
		$p = empty($p) ? 1 : $p;

		/* 获取详细信息 */
		$Document = D('Product');
		$info = $Document->detail($id);
		if(!$info){
			$this->error($Document->getError());
		}
		/* 分类信息 */
		$category = $this->category($info['category_id']);
		//导航信息
		$this->NowAddress($id,"detail");
		//底部推荐奖品
		$reco=$this->getOrderList();
		/* 更新浏览数 */
		$Document->where('id='.$id)->setInc('view');
		/*获取系列图片*/
		if ($info["picture"]!=""){
			$where["id"][]="in";
			$where["id"][]=$info["picture"];
			$result = M('picture')->where($where)->select();
			for($i=0; $i<count($result);$i++){
				$result[$i]["photo"]=picture($result[$i]["id"]);
			}
			$this->assign('picture', $result);
		}
		/*参与记录*/
		$map5['pro_id'] = $info['id'];
		$map5['mynum']= array('neq',0);
		$ordlist = M('Orderlist')->where($map5)->order('id desc')->select();
		$length = count($ordlist);
		for($i=0;$i<$length;$i++){
			$ord = M('Order')->where('id='.$ordlist[$i]['order_id'])->find();
			$ordlist[$i]['create_time'] = $ord['create_time'];
			$ordlist[$i]['nickname'] = get_nickname($ordlist[$i]['uid']);
			$ordlist[$i]['photo'] = M('Member')->where('uid='.$ordlist[$i]['uid'])->getField('picture');
			
			
		}
		$this->assign('ordlist',$ordlist);
		/*晒单分享*/
		$map1['status']= array('neq',-1);
		$map1['title'] = $info['title'];
		$products = M('Product')->where($map1)->getField('id',true);
		$map2['status']= array('neq',-1);
		$map2['pro_id']=array('in',$products);
		$uPrints = M('Prints')->where($map2)->select();
		for($i=0;$i<count($uPrints);$i++){
			$head = M('Product')->where('id='.$uPrints[$i]['pro_id'])->find();
			$uPrints[$i]['title'] = $head['title'];
			$uPrints[$i]['periods'] = $head['periods'];
			$user = M('Member')->where('uid='.$uPrints[$i]['uid'])->find();
			$uPrints[$i]['photo'] = $user['picture'];
			$uPrints[$i]['nickname'] = $user['nickname'];
			$uPrints[$i]['pic'] = explode(',', $uPrints[$i]['pic']);
		}
		$this->assign('uPrints',$uPrints);
		/* 模板赋值并渲染模板 */
		if($info['state']==0){
			/*开奖信息*/
			$search['status']=array('neq',-1);
			$search['state'] = 2;
			$search['title'] = $info['title'];
			$periods = M('Product')->where($search)->select();
			$award = $this->awrad($periods[0]['id']);
			$this->assign('award',$award);
			$this->assign('periods',$periods);
			$tpl = 'Product/detail';
		}else{
			/*最新一期正在进行*/
			$wheres['state'] = 0;
			$wheres['status'] = array('neq',-1);
			$wheres['title'] = $info['title'];
			$newPer = M('Product')->where($wheres)->find();
			$this->assign('newPer',$newPer);
			/*50个时间*/
			$timeall = 0;
			$map['mynum']= array('neq',0);
			$map['pro_id']=$id;
			$last = M('Orderlist')->where($map)->order('id desc')->find();
			$ord = M('Order')->where('id='.$last['order_id'])->find();
			$map4['create_time'] = array('lt',$ord['create_time']);
			$map4['status'] = 2;
			$orders = M('Order')->where($map4)->order('id desc')->limit(50)->select();
			for($j=0;$j<50;$j++){
				$orders[$j]['nickname'] = get_nickname($orders[$j]['uid']);
				$ordersli = M('Orderlist')->where('order_id='.$orders[$j]['id'])->select();
				$orders[$j]['title'] = $ordersli[0]['title'];
				$orders[$j]['length'] = 0;
				for($k=0;$k<count($ordersli);$k++){
					if($ordersli[$k]['title']==$orders[$j]['title'])
						$orders[$j]['length']++;
				}
				$timeall+=microtime_format('Hisx',$orders[$j]['create_time']);					
			}
			$this->assign('timeall',$timeall);
			$this->assign('lastTime',$ord['create_time']);
			$this->assign('orders',$orders);
			/*开奖信息*/
			if($info['state']==2){
				$userdata = M('Member')->where('uid='.$info['awarduser'])->find();
				$order_id=M('Orderlist')->where('mynum='.$info['awardnum'])->getField('order_id');
				$userdata['join_time'] =M('Order')->where('id='.$order_id)->getField('create_time');
				$map3['pro_id'] = $info['id'];
				$map3['uid'] = $info['awarduser'];
				$order=M('Orderlist')->where($map3)->select();
				$userdata['join_count'] = count($order);
				$this->assign('userdata',$userdata);
			}
			$tpl = 'Product/results';
		}
		$this->assign('sertime',NOW_TIME);
		$this->assign('category', $category);
		$this->assign('info', $info);
		$this->assign('page', $p); //页码
		$this->assign('reco',$reco);//推荐奖品
		$this->display($tpl);
	}
	
	//获取开奖信息
	public function awrad($id){
		$map['id'] = $id;
		$award = M('Product')->where($map)->find();
		if($award){
			$award['photo'] = M('Member')->where('uid='.$award['awarduser'])->getField('picture');
			$map2['pro_id'] = $award['id'];
			$map2['mynum'] = $award['awardnum'];
			$order_id=M('Orderlist')->where($map2)->getField('order_id');
			$award['join_time'] =M('Order')->where('id='.$order_id)->getField('create_time');
			$map1['pro_id'] = $award['id'];
			$map1['uid'] = $award['awarduser'];
			$order=M('Orderlist')->where($map1)->select();
			$award['join_count'] = count($order);
			return $award;
		}else{
			return null;
		}
		
	}
	//获取开奖信息jquery
	public function getAward(){
		$map['id'] = $_GET['id'];
		$award = M('Product')->where($map)->find();
		$award['photo'] = thumb(M('Member')->where('uid='.$award['awarduser'])->getField('picture'),82,82);
		$award['awardusername'] = get_nickname($award['awarduser']);
		$map2['pro_id'] = $award['id'];
		$map2['mynum'] = $award['awardnum'];
		$order_id=M('Orderlist')->where($map2)->getField('order_id');
		$award['join_time'] =microtime_format('Y-m-d H:i:s.x',M('Order')->where('id='.$order_id)->getField('create_time'));
		$award['update_time'] = microtime_format('Y-m-d H:i:s.x',$award['update_time']);
		$award['url'] = U('Product/detail?id='.$award['id']);
		$map1['pro_id'] = $award['id'];
		$map1['uid'] = $award['awarduser'];
		$order=M('Orderlist')->where($map1)->select();
		$award['join_count'] = count($order);
		echo json_encode($award);
		exit();
	}
	/*搜索页面*/
	public function Search($p = 1){
		$text = $_GET['text_ket'];
		if($text=="全部奖品"||$text=="最新奖品"){
			$Product = D('Product');
			$list_length=$Product->lists(null,0,'`create_time` DESC');
			$list = $Product->page($p,24)->lists(null,0,'`create_time` DESC');
		
		}else{
			if(count($_GET)>0){
				$where['title'] = $text;   
			}else{
				$where = I('post.');
			}
			$Product = D('Product');
			$list_length=$Product->Fuzzy($where, '`level` DESC,`id` DESC', 1,true,true);
			$list = $Product->page($p,24)->Fuzzy($where, '`level` DESC,`id` DESC', 1,true,true);
		}
		$this->assign('text', $text);
		$this->assign('News_list', $list);
		$this->assign('Lengths', count($list_length));
		$this->display("Product/lists");
	}
	/**
	 * 当前位置
	 * @param unknown $id
	 * @param unknown $type
	 */
	public function NowAddress(){
		$Category = D("Procate");
		//自动设置类型
		$cate = $_REQUEST;
		if(isset($cate['category'])){
			$info = $Category->info($cate['category']);
		}elseif (isset($cate['category']) && isset($cate['id'])){
			$info = $Category->info($cate['category']);
		}else{
			$info = D('Product')->detail($cate['id']);
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
					$theArray[] = array('title'=>$info['title'],'url'=>U('Product/index?category='.$info['name']));
				}else{
					$result = $Category->getTopDesc($info['id']);
					$theArray = $this->AutoUrl($result);
				}
				break;
			case 1:
				$theArray[] = array('title'=>$info['title'],'url'=>U('Product/intro?category='.$info['name']));
				if(isset($cate['category']) && isset($cate['id'])){
					$detail = M('Product')->where('id='.$cate['id'])->find();
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
				$url = U('Product/index?category='.$theArray[$i]['name']);
			}else{
				$url = U('Product/lists?category='.$theArray[$i]['name']);
			}
			$newArray[] = array('title'=>$theArray[$i]['title'],'url'=>$url);
		}
		return $newArray;
	}
	/*获取3d福彩中奖号码*/
	public function  getNumber(){
		$srcurl = "http://caipiao.163.com/award/3d/"; 
		$content=file_get_contents($srcurl);
		$start_position=strpos($content,'<p id="zj_area">开奖号码： '); 
		$start_position=$start_position+strlen('<p id="zj_area">开奖号码： '); 
		$end_position=strpos($content,'<span class="tryNum">'); 
		$length=$end_position-$start_position; 
		$content=substr($content,$start_position,$length);
		$content=findNum($content);
		echo $content;
		exit();
	}
	
	public function get_server_to_time($etime){
		$etime = str_replace("/","-",$etime);
		echo intval(strtotime($etime)) - intval(time());
		exit;
	}
	
	/*开奖*/
	public function UpdatePro(){
		$id = intval($_POST['id']);
		if($id){
			$pro = M('Product')->where('id='.$id)->find();
			if($pro['state']==1){
				$data['state'] = 2;
				$data['lottery'] = $_POST['lottery']*1;
				$data['time_total'] =$_POST['time_total']*1;
				$data['update_time'] =$pro['update_time'];
				$data['awardnum'] = intval(bcmod(($data['time_total']+$data['lottery']),$pro['total']))+10000001;
				$map['mynum'] = $data['awardnum'];
				$map['pro_id'] = $id;
				$uid = M('Orderlist')->where($map)->getField('uid');
				$data['awarduser'] = $uid;
				$res = M('Product')->where('id='.$id)->save($data);
				echo $res;
				exit();
			}else {
				echo '已开奖';
				exit();
			}
		}else{
			echo '已开奖';
			exit();
		}
	}
	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}
		/* 获取分类信息 */
		$category = D('Procate')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}
	//无限极联动
	public function Joint($id){
		$City= M('linkage')->where(array("pid"=>$id))->select();
		if(count($City)>0){
			echo("[");
			$j=1;
			for($i=0;$i<count($City);$i++){
				echo("{id:".$City[$i]["id"].",title:'".$City[$i]["title"]."'}");
				if($j<>count($City)){
					echo(",");
				}
				$j=$j+1;
			}
			echo("]");
		}
	}
}