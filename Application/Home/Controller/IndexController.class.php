<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Wechat\Controller;

use OT\DataDictionary;
use Admin\Controller\PublicController;
use Common\Controller\QRcode;
use Common\Controller\word;
use Common\Controller\Wechat;

/**
 * 微信首页控制器
 */
class IndexController extends HomeController {
	
	public function teamdesc($uid){
		$result = get_team_desc($uid);
		dump($result);
		exit;
	}
	
	public function jifen_new(){
		die("123");
		M("Member")->where("1=1")->setField("jifen",0);
		
		$time = "2016-03-01";
		$time = strtotime($time);
		
		$where = array();
		$where['create_time'] = array("gt",$time);
		$where['status'] = array("in","2,3");
		$list = M("Order")->where($where)->select();
		
		for($i=0;$i<count($list);$i++){
			$jifen = array();
			$map = array();
			$map['order_id'] = $list[$i]['id'];
			$orderlist = M("orderlist")->where($map)->select();
			for($j=0;$j<count($orderlist);$j++){
				$where = array();
				$where['id'] = $orderlist[$j]['pro_id'];
				$tjf = M('Product')->where($where)->getField("jifen");
				$jifen[] = $tjf * $orderlist[$j]['length'];
			}
			$jifen = array_sum($jifen);
			$where = array();
			$where['uid'] = $list[$i]['uid'];
			M('Member')->where($where)->setInc("jifen",$jifen);
		}
		
		
		
	}
	
	
	public function pro999(){
		die("die!!!");
		$map = array();
		$map['pro_id'] = 570;
		$ids = M("orderlist")->where($map)->getField("order_id",true);
		$map = array();
		$map['id'] = array("in",implode(",",$ids));
		$map['status'] = array("gt",0);
		$ids = M("Order")->where($map)->getField("id",true);
		
		foreach ($ids as $id){
			
			$map = array();
			$map['order_id'] = $id;
			$map['pro_id'] = 570;
			$count = M("orderlist")->where($map)->count();
			
			$price = $count * 9999;
			M("Order")->where("id=".$id)->setDec("pro_price",$price);
		}
		
	}
	
	
	public function auto_yf(){
		
		$str = "2015-12-28";
		$time = strtotime($str);
		$map = array();
		$map['reg_time'] = array("gt",$time);
		$ids = M("ucenter_member")->where($map)->getField("id",true);
		//$ids = array();
		foreach ($ids as $id){
			$puid = get_user_puid($id);
			if($puid){
				//自然注册
				$data = array();
				$data['uid'] = $id;
				$data['puid'] = $puid;
				$data['create_time'] = M("ucenter_member")->where("id=".$id)->getField("reg_time");
				$data['yf_type'] = 0;
				$data['val'] = 1;
				M("youfen")->add($data);
				M("Member")->where("uid=".$puid)->setInc("youfen");//优分+1
				
				//成功消费
				$map = array();
				$map['uid'] = $id;
				$map['create_time'] = array("gt",$time);
				$map['status'] = array("in","2,3");
				$order = M("Order")->where($map)->find();
				if($order){
					$data = array();
					$data['uid'] = $id;
					$data['puid'] = $puid;
					$data['create_time'] = M("ucenter_member")->where("id=".$id)->getField("reg_time");
					$data['yf_type'] = 1;
					$data['val'] = 1;
					M("youfen")->add($data);
					M("Member")->where("uid=".$puid)->setInc("youfen");//优分+1
				}				
			}
		}
		
	}
	
	
	// 系统首页
	public function index() {
		/* 精品 */
		$jpwhere ['position'] = array (
				"gt",
				0 
		);
		$jpwhere ['status'] = 1;
		$jingcai = M ( 'Product' )->where ( $jpwhere )->limit ( 5 )->select ();
		$this->assign ( "jingcai", $jingcai );
		/* 爆款 */
		$bkwhere ['position'] = array (
				"gt",
				1 
		);
		$bkwhere ['status'] = 1;
		$baokuan = M ( 'Product' )->where ( $bkwhere )->limit ( 2 )->order ( "id desc" )->select ();
		$this->assign ( "baokuan", $baokuan );
		
		/* 分类产品 */
		$tree = D ( 'Procate' )->getTree ( array (
				"in",
				"321,306,287,313,318" 
		) );
		$theArray = array ();
		for($i = 0; $i < count ( $tree ); $i ++) {
			$theArray [$i] ['pid'] = $tree [$i] ['pid'];
			$theArray [$i] ['title'] = $tree [$i] ['title'];
			$theArray [$i] ['name'] = $tree [$i] ['name'];
			$theArray [$i] ['icon'] = $tree [$i] ['icon'];
			$theArray [$i] ['url'] = $tree [$i] ['url'];
			$list = D ( 'Product' )->lists ( $tree [$i] ['id'] );
			$theArray [$i] ['list'] = $list;
		}
		$this->assign ( "catelist", $theArray );
		$this->assign ( "sharetitle", "首页-" );
		$this->assign ( "shareurl", "http://" . $_SERVER ['HTTP_HOST'] . U ( 'Index/index' ) );
		
		$ads = M ( 'ads' )->where ( 'pid=30' )->select ();
		$this->assign ( "ads", $ads );
		
		$slide_status =  M("ads_sort")->where("id=30")->getField("status");
		$this->assign("slide_status",$slide_status);
		
		
		$jpads = M ( 'ads' )->where ( 'pid=41' )->find ();
		$this->assign ( "jpads", $jpads );
		$jp_status =  M("ads_sort")->where("id=41")->getField("status");
		$this->assign("jp_status",$jp_status);
		
		$djads = M ( 'ads' )->where ( 'pid=42' )->find ();
		$this->assign ( "djads", $djads );
		$dj_status =  M("ads_sort")->where("id=42")->getField("status");
		$this->assign("dj_status",$dj_status);
		
		$gg = M ( 'ads' )->where ( 'pid=43' )->find ();
		$this->assign("gg",$gg);


		$mrthq = M("ads")->where("pid=44")->find();
		$this->assign("mrthq",$mrthq);
		$mrthq_status = M("ads_sort")->where("id=44")->getField("status");
		$this->assign("mrthq_status",$mrthq_status);

		
		$ggstatus = M("ads_sort")->where("id=43")->getField("status");
		$this->assign("ggstatus",$ggstatus);


		$mrthq = M("ads")->where("pid=46")->find();
		$this->assign("zhiding",$mrthq);
		$mrthq_status = M("ads_sort")->where("id=46")->getField("status");
		$this->assign("zhiding_status",$mrthq_status);
		$this->display ();
	}
	
	
	
	public function tmp(){
		
		$data['orderid'] = 1024;
		$data['uid'] = 791;
		$data['nowmoney'] = 100;
		$data['aftermoney'] = 200;
		
		
		$this->wechat = new Wechat (); // 实例化 wechat 类
		$url = "http://".C('WEB_SITE_DOMAIN')."/user/index.html";
		$content ['first'] = "订单号：".$data['orderid']."已确认收货，购买用户：".$data['uid']."，分佣用户：".$data['uid'];
		$content ['keyword1'] = "CheeWoPHP";
		$content ['keyword2'] = "线上分佣：".$data['nowmoney'];
		$content ['remark'] = "之前线上金额：".$data['aftermoney']."，分之后金额：".$data['nowmoney']."，分佣合计：".$data['aftermoney'];
		$url = "http://uxiango.2k6k.com";
		$this->wechat->tplmsg ( "omLoBuK6wrYLbbGfE2MoEj8HwgLU", "2G7_sk5HG06uJ9KDNS7da38P20rQWHC9PjCSrJcLU2A", $url, $content );
		
	}
	
	public function noreg(){
		
		$where['headimgurl'] = array("eq","");
		$where['openid'] =  array("eq","");
		$list = M('ucenter_member')->where($where)->select();
		dump($list);
		exit;
		
	}
	
	
	public function aaa111() {
		
		$word = new com("word.application") or die("Unable to instantiate Word");
		echo "Loaded Word, version {$word->Version}\n";
		
		exit;
		
		header ( "Content-Type:   application/msword" );
		header ( "Content-Disposition:   attachment;   filename=doc.doc" ); // 指定文件名称
		header ( "Pragma:   no-cache" );
		header ( "Expires:   0" );
		$html = '<table border="1" cellspacing="2" cellpadding="2" width="90%" align="center">';
		$html .= '<tr bgcolor="#cccccc"><td align="center">博客</td></tr>';
		$html .= '<tr bgcolor="#f6f7fa"><td><span style="color:#FF0000;"><strong>PHP将网页代码导出word文档</strong></span></td></tr>';
		$html .= '<tr><td align="center"><img src="http://uxiango.2k6k.com/index/a2"></td></tr>'; // 自定义图片文件
		$html .= '</table>';
		echo $html;
	}
	
	public function a2(){
		Vendor ( 'jpgraph.jpgraph' );
		Vendor ( 'jpgraph.jpgraph_bar' );
		
		$datay=array(20,30,50,80);
		$datay2=array(430,645,223,690);
		$datazero=array(0,0,0,0);
		
		// Create the graph.
		$graph = new \Graph(450,200);
		$graph->title->Set('Example with 2 scale bars');
		
		// Setup Y and Y2 scales with some "grace"
		$graph->SetScale("textlin");
		$graph->SetY2Scale("lin");
		$graph->yaxis->scale->SetGrace(30);
		$graph->y2axis->scale->SetGrace(30);
		
		//$graph->ygrid->Show(true,true);
		$graph->ygrid->SetColor('gray','lightgray@0.5');
		
		// Setup graph colors
		$graph->SetMarginColor('white');
		$graph->y2axis->SetColor('darkred');
		
		
		// Create the "dummy" 0 bplot
		$bplotzero = new \BarPlot($datazero);
		
		// Create the "Y" axis group
		$ybplot1 = new \BarPlot($datay);
		$ybplot1->value->Show();
		$ybplot = new \GroupBarPlot(array($ybplot1,$bplotzero));
		
		// Create the "Y2" axis group
		$ybplot2 = new \BarPlot($datay2);
		$ybplot2->value->Show();
		$ybplot2->value->SetColor('darkred');
		$ybplot2->SetFillColor('darkred');
		$y2bplot = new \GroupBarPlot(array($bplotzero,$ybplot2));
		
		// Add the grouped bar plots to the graph
		$graph->Add($ybplot);
		$graph->AddY2($y2bplot);
		
		// .. and finally stroke the image back to browser
		$graph->Stroke();
		
	}
	
	
	public function userhb($id) {
		$where['id'] = $id;
		$info = M('userhb')->where($where)->find();
		$wechatid = get_wechatid ();
		$wechatinfo = get_wechatinfo_by_id ( $wechatid );
		if (! isset ( $_REQUEST ['code'] )) {
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $wechatinfo ['appID'] . "&redirect_uri=" . urlencode ( "http://".C('WEB_SITE_DOMAIN')."/index/userhb/id/".$id.".html" ) . "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
			echo "<script>location.href='" . $wxurl . "'</script>";
		} else {
			$userinfo = session ( "userinfo" );
			$userinfo = json_decode ( $userinfo, true );
			$where = array();
			$where ['openid'] = $userinfo ['openid'];
			$uid = M ( 'ucenter_member' )->where ( $where )->getField ( "id" );
			if (! $uid) {
				$uid = 0;
				$fromopenid = session ( "fromopenid" );
				$where ['openid'] = $fromopenid;
				$frominfo = M ( 'ucenter_member' )->where ( $where )->find ();
				$this->assign ( "frominfo", $frominfo );
			}
			$userinfo = session ( "userinfo" );
			$userinfo = json_decode ( $userinfo, true );
			$this->assign ( "userinfo", $userinfo );
			$this->assign ( "uid", $uid );
			
			$this->assign("info",$info);
			
			$this->display ();
		}
	}
	
	public function hb($id) {
		$where['id'] = $id;
		$info = M('Coupons')->where($where)->find();
		$wechatid = get_wechatid ();
		$wechatinfo = get_wechatinfo_by_id ( $wechatid );
		if (! isset ( $_REQUEST ['code'] )) {
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $wechatinfo ['appID'] . "&redirect_uri=" . urlencode ( "http://".C('WEB_SITE_DOMAIN')."/index/hb/id/".$id.".html" ) . "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
			echo "<script>location.href='" . $wxurl . "'</script>";
		} else {
			$userinfo = session ( "userinfo" );
			$userinfo = json_decode ( $userinfo, true );
			$where = array();
			$where ['openid'] = $userinfo ['openid'];
			$uid = M ( 'ucenter_member' )->where ( $where )->getField ( "id" );
			if (! $uid) {
				$uid = 0;
				$fromopenid = session ( "fromopenid" );
				$where ['openid'] = $fromopenid;
				$frominfo = M ( 'ucenter_member' )->where ( $where )->find ();
				$this->assign ( "frominfo", $frominfo );
			}
			$userinfo = session ( "userinfo" );
			$userinfo = json_decode ( $userinfo, true );
			$this->assign ( "userinfo", $userinfo );
			$this->assign ( "uid", $uid );
				
			$this->assign("info",$info);
				
			$this->display ();
		}
	}
	
	public function byk() {
		$wechatid = get_wechatid ();
		$wechatinfo = get_wechatinfo_by_id ( $wechatid );
		if (! isset ( $_REQUEST ['code'] )) {
			$wxurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $wechatinfo ['appID'] . "&redirect_uri=" . urlencode ( "http://".C('WEB_SITE_DOMAIN')."/index/byk.html" ) . "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
			echo "<script>location.href='" . $wxurl . "'</script>";
		} else {
			$userinfo = $this->userinfo ;
			$where ['openid'] = $userinfo ['openid'];
			$uid = M ( 'ucenter_member' )->where ( $where )->getField ( "id" );
			if (! $uid) {
				$uid = 0;
				$fromopenid = session ( "fromopenid" );
				$where ['openid'] = $fromopenid;
				$frominfo = M ( 'ucenter_member' )->where ( $where )->find ();
				$this->assign ( "frominfo", $frominfo );
			}
			$this->assign ( "uid", $uid );
			$this->display ();
		}
	}
	
	public function lquserhb($pid) {
		$userinfo = session ( "userinfo" );
		$userinfo = json_decode ( $userinfo, true );
		$where ['openid'] = $userinfo ['openid'];
		$uid = M ( 'ucenter_member' )->where ( $where )->getField ( "id" );
		if (! $uid) {
			echo "0";
			exit ();
		} else {
			$map2 ['id'] = $pid;
			$info = M ( 'userhb' )->where ( $map2 )->find ();
			if (! $info) {
				// 红包不存在
				echo "-1";
				exit;
			}
			$map ['pid'] = $pid;
			$count = M ( 'userhb_log' )->where ( $map )->count ();
			if ($count<$info['times']) {
				//还有领取次数
				$map3['pid'] = $pid;
				$map3['uid'] = $uid;
				$user_count = M ( 'userhb_log' )->where ( $map3 )->count ();
				if($user_count){
					//已经领过该红包了！
					echo "-2";
					exit;
				}else{
					$data = array();
					$data ['uid'] = $uid;
					$data ['pid'] = $pid;
					if($info['hb_type']==1){
						//普通红包
						$data ['money'] = $info ['money'];
					}else if($info['hb_type']==2){
						//拼手气红包
						if(($info['times']-$count)==1){
							$map = array();
							$map ['pid'] = $pid;
							$moneysum = M ( 'userhb_log' )->where ( $map )->sum("money");
							$data['money'] = $info['countmoney'] - $moneysum;
						}else{
							if($info['countmoney']/$info['times']==0.01){
								$data['money'] = 0.01;
							}else{
								$map = array();
								$map ['pid'] = $pid;
								$moneysum = M ( 'userhb_log' )->where ( $map )->sum("money");
								$lastmoney = ($info['countmoney'] - $moneysum)*0.5;
								$randmoney = 0;
								do{
									$randmoney = randomFloat(0,$lastmoney);
								} while($randmoney==$lastmoney);
								$randmoney = round($randmoney,2);
								$data['money'] = $randmoney;
							}
						}
					}
					$data ['create_time'] = NOW_TIME;
					$res = M ( 'userhb_log' )->add ( $data );
					if($res){
						echo $data['money'];
						exit;
					}
				}
			} else {
				//没有领取次数了
				echo "-3";
				exit ();
			}
		}
	}
	
	
	
	public function lqbyj($pid) {
		$userinfo = session ( "userinfo" );
		$userinfo = json_decode ( $userinfo, true );
		$where ['openid'] = $userinfo ['openid'];
		$uid = M ( 'ucenter_member' )->where ( $where )->getField ( "id" );
		if (! $uid) {
			echo "0";
			exit ();
		} else {
			// 判断适用时间
			$map2 ['id'] = $pid;
			$map2 ['end_time'] = array (
					"gt",
					NOW_TIME 
			);
			$gz = M ( 'coupons' )->where ( $map2 )->find ();
			if (! $gz) {
				// 暂时取消
				// echo "已过领取时间！";
				// exit;
			}
			
			// 判断适用级别
			$nowgroup = M ( 'auth_group_access' )->where ( "uid=" . $uid )->getField ( "group_id" );
			if (! in_array ( $nowgroup, explode ( ",", $gz ['group'] ) )) {
				echo "不符合级别";
				exit ();
			}
			
			$map ['uid'] = $uid;
			$map ['pid'] = $pid;
			$id = M ( 'coupons_list' )->where ( $map )->count ();
			if ($id) {
				if ($id < $gz ['times']) {
					// 可以继续领
					$data ['uid'] = $uid;
					$data ['pid'] = $pid;
					$data ['length'] = 1;
					$data ['update_time'] = NOW_TIME;
					$data ['money'] = $gz ['gomoney'];
					$res = M ( 'coupons_list' )->add ( $data );
					if ($res) {
						echo "1";
					} else {
						echo "领取失败！";
					}
					exit ();
				} else {
					// 不能继续领
					echo "不能继续领取";
					exit ();
				}
			} else {
				$data ['uid'] = $uid;
				$data ['pid'] = $pid;
				$data ['length'] = 1;
				$data ['update_time'] = NOW_TIME;
				$data ['money'] = $gz ['gomoney'];
				$res = M ( 'coupons_list' )->add ( $data );
				if ($res) {
					echo "1";
				} else {
					echo "领取失败！";
				}
				exit ();
			}
		}
	}
	
	public function tongbu() {
		$where ['id'] = array (
				"gt",
				695 
		);
		$list = M ( 'ucenter_member' )->where ( $where )->select ();
		for($i = 0; $i < count ( $list ); $i ++) {
			if (! $list [$i] ['headimgurl']) {
				$map ['openid'] = $list [$i] ['openid'];
				$info = M ( 'wechat_user' )->where ( $map )->find ();
				if ($info ['headimgurl']) {
					$data ['headimgurl'] = $info ['headimgurl'];
					$data ['username'] = $info ['nickname'];
					M ( 'ucenter_member' )->where ( 'id=' . $list [$i] ['id'] )->save ( $data );
					$mdata ['nickname'] = $info ['nickname'];
					M ( 'Member' )->where ( 'uid=' . $list [$i] ['id'] )->save ( $mdata );
					dump ( $list [$i] );
				} else {
					$data ['username'] = $list [$i] ['mobile'];
					M ( 'ucenter_member' )->where ( 'id=' . $list [$i] ['id'] )->save ( $data );
					$mdata ['nickname'] = $list [$i] ['mobile'];
					M ( 'Member' )->where ( 'uid=' . $list [$i] ['id'] )->save ( $mdata );
					dump ( $list [$i] );
				}
			}
		}
	}
	public function huafei() {
		$this->assign ( "info", array (
				'title' => '话费充值' 
		) );
		$this->display ();
	}
	public function smstest() {
		// 智验apiKey
		$apiKey = "5ab6c721698e4d46be0710dd55c134da";
		// 应用appId
		$appId = "i8Dou9n99426";
		// 应用绑定模板ID
		$templateId = "RSETSESEKESE";
		// 手机号
		$mobile = "18628965391";
		// 参数
		$param = rand ( 1000, 9999 ) . ",2分钟";
		$url = "https://sms.zhiyan.net/sms/template_send.json";
		$json_arr = array (
				"mobile" => $mobile,
				"param" => $param,
				"templateId" => $templateId,
				"appId" => $appId,
				"apiKey" => $apiKey,
				"extend" => "",
				"uid" => "" 
		);
		$array = json_encode ( $json_arr );
		// 调用接口
		// 初始化curl
		$ch = curl_init ();
		// 参数设置
		$res = curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $array );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		echo ($result);
		return $result;
	}
	
	/**
	 * 手机客户端用拨打电话
	 *
	 * @param unknown $tel        	
	 */
	public function tel($tel) {
		echo "<script>location.href='tel:" . $tel . "'</script>";
	}
	
	/**
	 * 显示地图
	 */
	public function map() {
		echo "<script>location.href='http://api.map.baidu.com/marker?location=104.101016,30.650518&title=我的位置&content=百度奎科大厦&output=html'</script>";
	}
	public function autouserstatus($pass) {
		if ($pass != "cheewo")
			die ( "未授权访问" );
		
		$auth_list = M ( 'auth_group_access' )->getField ( "uid", true );
		foreach ( $auth_list as $value ) {
			
			$map ['uid'] = $value;
			$map ['status'] = array (
					"gt",
					1 
			);
			$count = M ( 'Order' )->where ( $map )->count ();
			if ($count > 0) {
				$where ['uid'] = $value;
				$data ['status'] = 1;
				M ( 'auth_group_access' )->where ( $where )->save ( $data );
			}
		}
	}
	public function qrcode($openid) {
		$this->assign ( "openid", $openid );
		$this->display ();
	}
	public function t11() {
		echo urlencode ( "http://uomouoyo.2k6k.com/index/voucher.html" );
	}
	public function zhuan() {
		$where ['group_id'] = array (
				"in",
				"12,13" 
		);
		$uid = M ( 'auth_group_access' )->where ( $where )->getField ( "uid", true );
		dump ( $uid );
		for($i = 0; $i < count ( $uid ); $i ++) {
			$info = M ( 'Member' )->where ( "uid=" . $uid [$i] )->find ();
			if ($info) {
				dump ( $info );
				$data ['money'] = $info ['xxmoney'];
				$data ['xxmoney'] = $info ['money'];
				dump ( $data );
				M ( 'Member' )->where ( "uid=" . $uid [$i] )->save ( $data );
			}
		}
	}
	public function fyback($id) {
		$where['order_id'] = $id;
		$where['money_type'] = array("in","0,1");
		$list = M ( 'MoneyLog' )->where ( $where )->select ();
		for($i = 0; $i < count ( $list ); $i ++) {
			if ($list [$i] ['money_type'] == 0) {
				$res = M ( 'Member' )->where ( "uid=" . $list [$i] ['uid'] )->setDec ( "xxmoney", $list [$i] ['money'] );
			}
			if ($list [$i] ['money_type'] == 1) {
				$res1 = M ( 'Member' )->where ( "uid=" . $list [$i] ['uid'] )->setDec ( "money", $list [$i] ['money'] );
			}
			M ( 'MoneyLog' )->where ( "id=" . $list [$i] ['id'] )->delete ();
		}
	}
}