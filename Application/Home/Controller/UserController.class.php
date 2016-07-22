<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;
use Common\Controller\Wechat;
use Think\AjaxPage;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {
	
	private $wechat;

	/* 用户中心首页 */
	public function index($p=1){
		if(HUID){
			$this->meta_title = '用户中心';
			//获取用户详细信息
			$info = M('Member')->where(array('uid'=>HUID))->find();
			$User = new UserApi;
			$info = array_merge($info,$User->infoall(HUID));//获取用户所有信息
			$this->assign('info',$info);
			//广告
			$ad3 = M("Ads")->where('id=31')->find();
			$this->assign('ad3',$ad3);
			//三个月
			$monThree=strtotime("-90 days");
			//本月
			$monOne = array('between',array(strtotime(date('Y-m-01 00:00:00')),strtotime(date('Y-m-d H:i:s'))));
			//本周
			$arr=getdate();
			$num=$arr['wday'];
			$week =array('between',array(time()-($num-1)*24*60*60,time()+(7-$num)*24*60*60));
			//今天
			$today=strtotime(date('Y-m-d 00:00:00'));
			//时间参数
			$time1 = $_GET['box1'];
			$time2 = $_GET['box2'];
			$time3 = $_GET['box3'];
			//获取用户订单
			$map=array();
			$map["uid"]=is_login();
			$map["status"]=array('neq',-1);
			if($time1){
				switch ($time1){
					case "all":
						break;
					case "today":
						$map['create_time'] = array('egt',$today);
						break;
				    case "week":
				    	$map['create_time'] = $week;
						break;
				    case "month":
				    	$map["create_time"] = $monOne;
						break;
				    case "last":
				    	$map["create_time"]=array('gt',$monThree);
				    	break;
				    case "before":
				    	$map["create_time"]=array('elt',$monThree);
				    	break;	
				    case "search":
				    	if ( isset($_POST['time-start']) ) {
				    		$map['create_time'][] = array('egt',strtotime(I('time-start')));
				    	}
				    	if ( isset($_POST['time-end']) ) {
				    		$map['create_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
				    	}
				    	break;
				}
				
			}//用户订单时间筛选
				$ord = M('Order')->where($map)->order('create_time desc')->select();
				for($i=0;$i<count($ord);$i++){
					$map5['order_id'] = $ord[$i]['id'];
					$map5['deletes'] = 0;
					$name = M('orderlist')->where($map5)->select();
					$ord[$i]['buy_time'] =date("Y/m/d H:i:s",strtotime("+10 minutes",$ord[$i]['create_time']));
					for($j=0;$j<count($name);$j++){
						$ord[$i][suborder][$j]["id"]=$name[$j]['id'];
						$ord[$i][suborder][$j]["picture"]=$name[$j]['picture'];
						$ord[$i][suborder][$j]["pro_id"]=$name[$j]['pro_id'];
						$pro=M('Product')->where('id='.$name[$j]['pro_id'])->find();
						$ord[$i][suborder][$j]["periods"] = $pro['periods'];
						$ord[$i][suborder][$j]["total"] = $pro['total'];
						$ord[$i][suborder][$j]["state"] = $pro['state'];
						$ord[$i][suborder][$j]["awardnum"] = $pro['awardnum'];
						$ord[$i][suborder][$j]["awarduser"] = get_username($pro['awarduser']);
						$where['uid'] = $pro['awarduser'];
						$where['pro_id'] =$pro['id'];
						$where['mynum'] = array('neq',0);
						$counts = M('Orderlist')->where($where)->select();
						$ord[$i][suborder][$j]["awardcount"]=count($counts);
						$ord[$i][suborder][$j]["update_time"] = $pro['update_time'];
						$ord[$i][suborder][$j]["title"]=$name[$j]['title'];
						$ord[$i][suborder][$j]["length"]=$name[$j]['length'];
						$ord[$i][suborder][$j]["mynum"]=$name[$j]['mynum'];
						
					}

				}
				$reco=$this->getOrderList();//推荐奖品
				//晒单
				$condition['status'] = array('neq',-1);
				$condition['uid'] = is_login();
				if($time3){
					switch ($time3){
						case "all":
							break;
						case "today":
							$condition['create_time'] = array('egt',$today);
							break;
						case "week":
							$condition['create_time'] = $week;
							break;
						case "month":
							$condition["create_time"] = $monOne;
							break;
						case "last":
							$condition["create_time"]=array('gt',$monThree);
							break;
						case "before":
							$condition["create_time"]=array('elt',$monThree);
							break;
						case "search":
							if ( isset($_POST['time-start']) ) {
								$condition['create_time'][] = array('egt',strtotime(I('time-start')));
							}
							if ( isset($_POST['time-end']) ) {
								$condition['create_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
							}
							break;
					}
				
				}
				$uPrints = M('Prints')->where($condition)->select();
				for($i=0;$i<count($uPrints);$i++){
					$pro = M('Product')->where('id='.$uPrints[$i]['pro_id'])->find();
					$uPrints[$i]['title'] = $pro['title'];
					$uPrints[$i]['periods'] = $pro['periods'];
					$uPrints[$i]['pic'] = explode(',', $uPrints[$i]['pic']);
				}
				//中奖记录
				$condition1['state']=2;
				$condition1['status']=array('neq',-1);
				$condition1['awarduser'] = HUID;
				if($time2){
					switch ($time2){
						case "all":
							break;
						case "today":
							$condition1['update_time'] = array('egt',$today);
							break;
						case "week":
							$condition1['update_time'] = $week;
							break;
						case "month":
							$condition1["update_time"] = $monOne;
							break;
						case "last":
							$condition1["update_time"]=array('gt',$monThree);
							break;
						case "before":
							$condition1["update_time"]=array('elt',$monThree);
							break;
						case "search":
							if ( isset($_POST['time-start']) ) {
								$condition1['update_time'][] = array('egt',strtotime(I('time-start')));
							}
							if ( isset($_POST['time-end']) ) {
								$condition1['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
							}
							break;
					}
				
				}
				$record = M('Product')->where($condition1)->order('update_time desc')->select();
				for($i=0;$i<count($record);$i++){
					$map1['pro_id'] = $record[$i]['id'];
					$map1['uid'] = $record[$i]['awarduser'];
					$order=M('Orderlist')->where($map1)->select();
					$record[$i]['join_count'] = count($order);
				}
				$this->assign('sertime',NOW_TIME);
				$this->assign('record',$record);//中奖记录
				$this->assign('uPrints',$uPrints);//晒单
				$this->assign('ord',$ord);//夺宝记录
				$this->assign('reco',$reco);//推荐奖品
			    $this->display();
		} else {
			$this->redirect('User/login');
		}
	}

	/* 注册页面 */
	public function register($username = '', $password = '', $repassword = '',$email = '',$mobile='', $verify = ''){
        if(!C('USER_ALLOW_REGISTER')){
            $this->error('注册已关闭');
        }
		if(IS_POST){ //注册用户
			/* 检测验证码 */
			if($verify!=session('code')){
				$this->error('验证码输入错误！');
			}
			/* 检测密码 */
			if($password != $repassword){
				$this->error('密码和重复密码不一致！');
			}
			/* 调用注册接口注册用户 */
            $User = new UserApi;
            $username=$mobile;
			$uid = $User->register($username, $password, $email,$mobile);
			if(0 < $uid){ //注册成功
				$data = I('param.');
				//添加记录到Member
				$data['uid'] = $uid;
				if(empty($data['nickname'])){
					$data['nickname'] = $data['username'];
				}
				if(!M('Member')->add($data)) $this->error('写入记录出错');
				
				//验证判断
				$verify = C('USER_YANZHENG');
				switch ($verify){
					case 1://不验证
						$uid = think_encrypt($uid,'userid');
						$this->success('注册成功！',U('complete?uid='.$uid));
						break;
					case 2://邮箱验证
						$info = $_POST;
						$info['http_host'] = $_SERVER['HTTP_HOST'];
						$encrypyid = think_encrypt($uid);//加密用户ID
						session('encrypyid',$encrypyid);
						$info['encrypyid'] = $encrypyid;
						$this->assign('info',$info);
						$html = $this->fetch('taglib/email/verify');//获取模板内容
						$subject = C('WEB_SITE_TITLE')."：验证您的电子邮件地址以完成帐户注册";
						if(send_mail($email,$subject,$html)){
							$this->redirect('emailverify');
						}
						break;
					default:
						$uid = think_encrypt($uid,'userid');
						$this->success('注册成功！',U('complete?uid='.$uid));
						break;
				}
			} else { //注册失败，显示错误信息
				$this->error($this->showRegError($uid));
			}
		} else { //显示注册表单
			$this->display();
		}
	}
	/**
	 * 注册获取手机验证码
	 * */
	public function smsverify($mobile){
		$mobile = $_GET['mobile'];
		$result = M('UcenterMember')->where('mobile='.$mobile)->find();
		if($result!=null){
			echo '该手机号码已经被注册，请重新输入！';
			exit();
		}else{
			$code = rand(100000,999999);
			session('code',$code);
			$content = "验证码：".$code."【99车盟汇】";
			$reback = $this->sendsms($mobile,$content);
			if($reback!=null){
				echo '短信验证码已发送，请耐心等待！';
				exit();
			}else{
				echo '短信发送失败，请重新发送！';
				exit();
			}
		}
	}
	public function sendsms($mobile=0,$content=''){
		$sms_appid = '1366';
		$sms_key = '53df2cacfd52804d4aff0ff17cbc986bdd7c601b';
		$url ="http://sms.bechtech.cn/Api/send/data/json?accesskey=".$sms_appid."&secretkey=".$sms_key."&mobile=".$mobile."&content=".urlencode($content);
		return file_get_contents($url);
	}

	/* 登录页面 */
	public function login($username = '', $password = '', $verify = ''){
		if(IS_POST){ //登录验证
			
			/*后台开启验证码验证时才验证，否则不验证*/
			if(C('USER_LOGIN_VERIFY')==1){
				/* 检测验证码 */
				if(!check_verify($verify)){
					$this->error('验证码输入错误！');
				}
			}
			
			/* 调用UC登录接口登录 */
			$user = new UserApi;
			$uid = $user->login($username, $password,3);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					//TODO:跳转到登录前页面
					//$this->success('登录成功！',U('User/index'));
					$this->redirect("Home/index");
				} else {
					$this->error($Member->getError());
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				$this->error($error);
			}
		} else { //显示登录表单
			$this->display();
		}
	}
	

	/* 退出登录 */
	public function logout(){
		if(is_login()){
			D('Member')->logout();
			$this->success('退出成功！', U('User/login'));
		} else {
			$this->redirect('User/login');
		}
	}
	/*完善个人资料页面*/
	public function complete(){
		if($_POST){
			if($_GET){
				$uid = think_decrypt($_GET['uid'],'userid');
			$data=$_POST;
			$data['uid'] = $uid;
			if($data['username']){
				unset($data['username']);
			}
			$re1 = D('Member')->save($data);
			$re2 = M('UcenterMember')->where('id='.$uid)->setField('username',$_POST['username']);
			if($re1&&$re2!==false){
				$this->success('保存资料成功！请登录',U('User/login'));
			}
			}else{
				$this->error('操作失败！');
			}
		}else{
	   		 $this->display();
		}
	}
	
	/**
	 *忘记密码
	 * */
	public function forget_pwd($verify = ''){
		if(IS_POST){
			/* 检测验证码 */
			if(!check_verify($verify)){
				$this->error('验证码输入错误！');
			}
			/* 查看该用户是否存在 */
			$nickname = M('Member')->where('nickname='.'"'.$_POST['user_name'].'"')->find();
			$mobile = M('ucenter_member')->where('mobile='.$_POST['user_name'])->find();
			if($nickname||$mobile){ //成功
				$_SESSION['uid'] = $nickname['uid']?$nickname['uid']:$mobile['id'];
				$this->redirect('verifys');
			} else { //失败，显示错误信息
				$this->error("该账户名不存在！请重新输入！",U('forget_pwd'));
			}
		} else {
			$this->display();
		}
	}
	/**
	 * 验证获取手机验证码
	 * */
	public function getVerify($mobile){
		$mobile = $_GET['mobile'];
		$code = rand(100000,999999);
		session('code',$code);
		$content = "验证码：".$code."【99车盟汇】";
		$reback = $this->sendsms($mobile,$content);
		if($reback!=null){
			echo '短信验证码已发送，请耐心等待！';
			exit();
		}else{
			echo '短信发送失败，请重新发送！';
			exit();
		}
	}
	
	/**
	 *验证用户
	 * */
	public function verifys(){
		if($_SESSION['uid']){
			$user = M('ucenter_member')->where('id='.$_SESSION['uid'])->find();
			$mobile = $user['mobile'];
		}
		if(IS_POST){
			if($_POST['verifys']==session('code')){
				$this->redirect('modifys');
			}else{
				$this->error("验证码错误！请重新输入！",U('verifys'));
			}
		}
		$this->assign('mobile',$mobile);
		$this->display();
	}
	/**
	 * 重置密码
	 * */
	public function modifys(){
		if(IS_POST){
			$user = new UserApi;
			$data['password'] = think_ucenter_md5($_POST['user_pwd'], UC_AUTH_KEY);
			$res = $user->update($_SESSION['uid'] , $data);
			if($res){
				$this->redirect('result');
			}else{
				$this->error("重置密码失败！",U('modifys'));
			}
		}
		$this->display();
	}
	/**
	 * 重置成功
	 * **/
	public function result(){
		$user = M('Member')->where('uid='.$_SESSION['uid'])->find();
		$this->assign('username',$user['mobile']);
		$this->display();
	}
	/**
	 * 头像设置
	 */
	public function photo(){
		if(IS_GET){
			$res = M('Member')->where('uid='.is_login())->setField('picture',$_GET['picture']);
			if($res){
				echo "设置照片成功！";
				exit;
			}else{
				echo "设置照片失败！";
				exit;
			}
		}
	}
	/**
	 * 昵称设置
	 */
	public function nickname(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		if(IS_GET){
			$res = M('Member')->where('uid='.is_login())->setField('nickname',$_GET['nickname']);
			if($res){
				echo "保存成功！";
				exit;
			}else{
				echo "保存失败！";
				exit;
			}
		}
	}
	/**
	 * qq设置
	 */
	public function qq(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		if(IS_GET){
			$res = M('Member')->where('uid='.is_login())->setField('qq',$_GET['qqnum']);
			if($res){
				echo "保存成功！";
				exit;
			}else{
				echo "保存失败！";
				exit;
			}
		}
	}
	/**
	 * 地址设置
	 */
	public function address(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		if(IS_GET){
			$res = M('Member')->where('uid='.is_login())->setField('address',$_GET['addr']);
			if($res){
				echo "保存成功！";
				exit;
			}else{
				echo "保存失败！";
				exit;
			}
		}
	}
	/*删除订单*/
	public function delOrd(){
		$id = $_POST['id'];
		$orderlist_id = $_POST['orderlist_id'];
		$orderlist = M('Orderlist')->where('id='.$orderlist_id)->find();
		$pro = M('Product')->where('id='.$orderlist['pro_id'])->find();
		$join=$pro['join']-$orderlist['length'];
		M('Product')->where('id='.$orderlist['pro_id'])->setField('join',$join);
		$res = M('Order')->where('id='.$id)->setField('status',-1);
		echo $res;
		exit();
	}
	/*隐藏订单*/
	public function hideOrd(){
		$id = $_GET['id'];
		$res = M('Orderlist')->where('id='.$id)->setField('deletes',1);
		if($res){
			 $this->success("删除成功！",U('index'));
		}else{
			$this->error("删除失败！",U('index'));
		}
	}
	/**
	 * 晒单发布
	 */
	public function prints(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		$pro_id = $_GET['id'];
		if($pro_id){
			$pro = M('Product')->where('id='.$pro_id)->find();
		}
		$this->assign('pro',$pro);
		$this->display();
	}
	public function addPrints(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login') );
		}
		//添加或修改
		$prints = D('Prints');//调用模型
		$res = $prints->update();
		if($res){
			$this->success("发布成功！",U('userPrints'));
		}else {
			$this->error('错误');
		}
		
	}
	/**
	 * 晒单列表
	 */
	public function userPrints(){
		$map['status']= array('neq',-1);
		$uPrints = M('Prints')->where($map)->select();
		for($i=0;$i<count($uPrints);$i++){
			$pro = M('Product')->where('id='.$uPrints[$i]['pro_id'])->find();
			$uPrints[$i]['cover_id'] = $pro['cover_id'];
			$uPrints[$i]['title'] = $pro['title'];
			$uPrints[$i]['periods'] = $pro['periods'];
			$uPrints[$i]['state'] = $pro['state'];
			$uPrints[$i]['awardnum'] = $pro['awardnum'];
			$user = M('Member')->where('uid='.$uPrints[$i]['uid'])->find();
			$uPrints[$i]['photo'] = $user['picture'];
			$uPrints[$i]['nickname'] = $user['nickname'];
		}
		$this->assign('uPrints',$uPrints);
		$this->assign('counts',count($uPrints));
		//底部推荐奖品
		$reco=$this->getOrderList();
		$this->assign('reco',$reco);//推荐奖品
		$this->display();
	}
	
	/* 验证码，用于登录和注册 */
	public function verify($id=1){
		$verify = new \Think\Verify();
		$verify->entry(1);
	}
	/**
	 * 获取用户注册错误信息
	 * @param  integer $code 错误编码
	 * @return string        错误信息
	 */
	private function showRegError($code = 0){
		switch ($code) {
			case -1:  $error = '用户名长度必须在16个字符以内！'; break;
			case -2:  $error = '用户名被禁止注册！'; break;
			case -3:  $error = '用户名被占用！'; break;
			case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
			case -5:  $error = '邮箱格式不正确！'; break;
			case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
			case -7:  $error = '邮箱被禁止注册！'; break;
			case -8:  $error = '邮箱被占用！'; break;
			case -9:  $error = '手机格式不正确！'; break;
			case -10: $error = '手机被禁止注册！'; break;
			case -11: $error = '手机号被占用！'; break;
			default:  $error = '未知错误';
		}
		return $error;
	}

    /**
     * 用户支付结果
     */
    public function myorder(){
    	$result = $_GET['ordtype'];
    	$reco=$this->getOrderList();
    	$this->assign('reco',$reco);
    	if($result=="payed"){
    		$ordid = $_GET['ord'];
    		$create_time = M('Order')->where('id='.$ordid)->getField('create_time');
    		$map['order_id'] = $ordid;
	    	$ord = M('Orderlist')->where($map)->select();
	        for($i=0;$i<count($ord);$i++){
	    		$ord[$i]['create_time'] = $create_time;
	    		$periods = M('Product')->where('id='.$ord[$i]['pro_id'])->getField('periods');
	    		$ord[$i]['periods'] = $periods;
	        }
    		$this->assign('ord',$ord);
    		$this->display('payed');
    	}else{
    		$this->display('unpay');
    	}
    }

}
