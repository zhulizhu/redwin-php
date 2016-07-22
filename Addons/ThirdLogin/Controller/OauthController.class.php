<?php
/**
 *@author: izhang
 *@date:   2014-1-14 上午06:16:51 
 *@
 * */
namespace Addons\ThirdLogin\Controller;
use Home\Controller\AddonsController; 
use Addons\ThirdLogin\Lib\ThinkOauth;
use User\Api\UserApi as UserApi;

class OauthController extends AddonsController{
	
	public function _initialize(){
		$addon_config = $this->getConfig();
		// qq互联 sdk配置
		$qzone_configs = array(
			'APP_KEY' => $addon_config['qq_qzone_akey'],
			'APP_SECRET' => $addon_config['qq_qzone_skey'],
			'CALLBACK' => "http://".$_SERVER['SERVER_NAME'].U('Home/addons/execute',array('_addons'=>'ThirdLogin','_controller'=>'Oauth','_action'=>'getQzoneAT'),true,false,true)
			);
		C('THINK_SDK_QZONE',$qzone_configs);

		// 新浪微博 sdk配置
		$sina_configs = array(
			'APP_KEY' => $addon_config['sina_wb_akey'],
			'APP_SECRET' => $addon_config['sina_wb_skey'],
			'CALLBACK' => "http://".$_SERVER['SERVER_NAME'].U('Home/addons/execute',array('_addons'=>'ThirdLogin','_controller'=>'Oauth','_action'=>'getSinaAT'),true,false,true)
			);
		C('THINK_SDK_SINA',$sina_configs);
		
	}
	
    //应用名称
    private static $validAlias   = array(
            'sina'      => '新浪微博', 
            'qzone'     => "QQ互联",
            'qq'        => '腾讯微博',
            'renren'    => "人人网",
            'douban'    => "豆瓣",
            'baidu'     => "百度",
            'taobao'    => "淘宝网",
    );	
	
	// QQ互联 登陆
	public function qzone(){
		if(I('get.do')=='bind'){
			$addon_config = $this->getConfig();
			// qq互联 sdk配置
			$qzone_configs = array(
				'APP_KEY' => $addon_config['qq_qzone_akey'],
				'APP_SECRET' => $addon_config['qq_qzone_skey'],
				'CALLBACK' => "http://".$_SERVER['SERVER_NAME'].U('Home/addons/execute',array('_addons'=>'ThirdLogin','_controller'=>'Oauth','_action'=>'getQzoneAT','do'=>'bind'),true,false,true)
				);
			C('THINK_SDK_QZONE',$qzone_configs);			
		}else if (I('get.do')=='qbind')	{//快捷设置qq登录
			$addon_config = $this->getConfig();
			// qq互联 sdk配置
			$qzone_configs = array(
				'APP_KEY' => $addon_config['qq_qzone_akey'],
				'APP_SECRET' => $addon_config['qq_qzone_skey'],
				'CALLBACK' => "http://".$_SERVER['SERVER_NAME'].U('Home/addons/execute',array('_addons'=>'ThirdLogin','_controller'=>'Oauth','_action'=>'getQzoneAT','do'=>'qbind','encode'=>session('encode')),true,false,true)
				);
			C('THINK_SDK_QZONE',$qzone_configs);				
		}
		
		
		$config =C('THINK_SDK_QZONE');
		if(empty($config['APP_KEY']) || empty($config['APP_SECRET'])){
			$this->error('请先填写app_key');
		}			
		//加载ThinkOauth类并实例化一个对象
        import("##/##/Addons.ThirdLogin.Lib.ThinkOauth");
        $sns  = ThinkOauth::getInstance('qzone');
        //print_r($sns->getRequestCodeURL());
        redirect($sns->getRequestCodeURL());
		
	}
	// QQ互联回调地址
	public function getQzoneAT(){
		$code = I('get.code');		
		$this->login('qzone',$code);
	}


	// 新浪微博登陆
	public function sina(){
		$config =C('THINK_SDK_SINA');
		if(empty($config['APP_KEY']) || empty($config['APP_SECRET'])){
			$this->error('请先填写app_key');
		}		
		
		//加载ThinkOauth类并实例化一个对象
        import("##/##/Addons.ThirdLogin.Lib.ThinkOauth");
        $sns  = ThinkOauth::getInstance('sina'); 
        //跳转到授权页面
        redirect($sns->getRequestCodeURL());
	}

	// 新浪微博回调地址
	public function getSinaAT(){
		$code = I('get.code');
		$this->login('sina',$code);
	}
	/**
	 * 用户登陆
	 */
	public function login($type = null, $code = null){

		//当前操作如果是绑定
		if ($_GET ['do'] == "bind") {
			$this->_bindPublish ( $type, $param['res'] );
			return ;
		}else if($_GET ['do'] == "qbind"){
			$this->_qbindPublish ( $type, $param['res'] );
			return ;
		}else{
        	//加载ThinkOauth类并实例化一个对象
        	import("##/##/Addons.ThirdLogin.Lib.ThinkOauth");
        	$sns  = ThinkOauth::getInstance($type);
			
        	$sns->checkUser ();//return ;
			$userinfo = $sns -> userInfo();
			
			//return ;
		    // 检查是否成功获取用户信息
            if ( empty ( $userinfo ['id'] ) || empty ( $userinfo ['uname'] )) {
                $result ['status']  = 0;
                $result ['url']     = SITE_URL;
                $result ['info']    = "获取用户信息失败";
                return;
            }
            
            //检查是否存在这个用户的登录信息
            if ( $info = M('login')->where ( "`type_uid`='" . $userinfo ['id'] . "' AND type='{$type}'" )->find () ) {
            	//获取用户信息
            	$user = M('ucenter_member')->field('password',true)->where ( "id=" . $info ['uid'] )->find ();
            	// 未在本站找到用户信息, 删除用户站外信息,让用户重新登录
            	if (empty ( $user )) {
            		M('Login')->where ( "type_uid=" . $userinfo ['id'] . " AND type='{$type}'" )->delete ();
            		//已经绑定过，执行登录操作，设置token
            	}else{
            		if ($info ['oauth_token'] == '') {
            			$syncdata ['login_id']  = $info ['login_id'];
            			$syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
            			$syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
            			M('Login')->save ( $syncdata );
            		}
            		$user = new UserApi;
            		$Member = D('Member');
            		if($Member->login($info['uid'])){
            
            			$this->assign('jumpUrl', U('user/index'));
            			$this->success('同步登录成功');
            		}else{
            			$this->error($Member->getError());
            		}
            		return ;
            	}
            }

            //没绑定过，去注册页面
            $this->assign ( 'user', $userinfo );
            $this->assign ( 'type', $type );
            $this->assign ( 'typeName', self::$validAlias[$type]);
            // 设置token的相关值
            $oauth_token = isset($_GET['oauth_token']) ? t($_GET['oauth_token']) : $_SESSION[$type]['access_token']['oauth_token'];
            $this->assign('oauth_token', $oauth_token);
            $oauth_token_secret = isset($_GET['oauth_token_secret']) ? t($_GET['oauth_token_secret']) : $_SESSION[$type]['access_token']['oauth_token_secret'];
            $this->assign('oauth_token_secret', $oauth_token_secret); 
            $this->display(T('Addons://ThirdLogin@./Default/login'));
		}
	}
	
    private function _bindPublish($type, &$result) {
        //加载ThinkOauth类并实例化一个对象
        import("##/##/Addons.ThirdLogin.Lib.ThinkOauth");
        $sns  = ThinkOauth::getInstance($type);
        $sns->checkUser ('bind');;

        // 检查是否成功获取用户信息
        $userinfo = $sns->userInfo();
      
        if (!isset($userinfo ['id']) || empty($userinfo ['uname'])) {
            $this->error('获取用户信息失败');
            return;
        }

        $syncdata ['uid'] = session('user_auth.uid');
        $syncdata ['type_uid'] = $userinfo ['id'];
        $syncdata ['type'] = $type;
        $syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
        $syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
       // $syncdata ['is_sync'] = ($_SESSION [$type] ['isSync'])?$_SESSION [$type] ['isSync']:'1';
        S('user_login_'.session('user_auth.uid'),null);
        
        if ($info = M ( 'login' )->where ( "type_uid={$userinfo['id']} AND type='" . $type . "'" )->find ()) {
            // 该新浪用户已在本站存在, 将其与当前用户关联(即原用户ID失效)
            M ( 'login' )->where ( "`login_id`={$info['login_id']}" )->save ( $syncdata );
        } else {
            // 添加同步信息
            M ( 'login' )->add ( $syncdata );
        }
		
        $this->success('绑定成功',U('member/clientarea/bind'));
    }	
    
    private function _qbindPublish($type, &$result) {
    	if(!(I('encode'))){
    		$this->error('获取key出错',U('user/login'));
    	}

        //加载ThinkOauth类并实例化一个对象
        import("##/##/Addons.ThirdLogin.Lib.ThinkOauth");
        $sns  = ThinkOauth::getInstance($type);
        $sns->checkUser ('bind');;

        // 检查是否成功获取用户信息
        $userinfo = $sns->userInfo();
      
        if (!isset($userinfo ['id']) || empty($userinfo ['uname'])) {
            $this->error('获取用户信息失败');
            return;
        }

        //根据MD5加密邮箱获取用户资料
        $udata = M('ucenter_member')->field('id,email,expired')->where(array('md5email'=>I('encode')))->find();
        
        
        $syncdata ['uid'] = $udata['id'];
        $syncdata ['type_uid'] = $userinfo ['id'];
        $syncdata ['type'] = $type;
        $syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
        $syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
       // $syncdata ['is_sync'] = ($_SESSION [$type] ['isSync'])?$_SESSION [$type] ['isSync']:'1';
        S('user_login_'.$udata['id'],null);
        
        if ($info = M ( 'login' )->where ( "type_uid={$userinfo['id']} AND type='" . $type . "'" )->find ()) {
            // 该新浪用户已在本站存在, 将其与当前用户关联(即原用户ID失效)
            M ( 'login' )->where ( "`login_id`={$info['login_id']}" )->save ( $syncdata );
        } else {
            // 添加同步信息
            M ( 'login' )->add ( $syncdata );
        }
        
        $user = new UserApi; 
		$Member = D('Member');                   
        if($Member->login($udata['id'])){	
			$this->success('绑定成功',U('member/clientarea/bind'));
		}else{
			$this->error($Member->getError());
		}		 
    }	    
    
    public function unbind(){
        if(session('user_auth.uid') > 0){
            $type = $_POST['type'];
            echo M("login")->where("uid=".session('user_auth.uid')." AND type='{$type}'" )->delete();
            S('user_login_'.session('user_auth.uid'),null);
        }else{
            echo 0;
        }
    }    
	
	/* 移动客户端外部帐号登录 */
	public function login_on_client(){
		
	}
	
	public function FunctionName($value='')
	{
		# code...
	}
    /**
     * 获取插件的配置数组
     */
    final public function getConfig(){
        static $_config = array();
        $name = 'ThirdLogin';
        if(isset($_config[$name])){
            return $_config[$name];
        }
        $config =   array();
        $map['name']    =   $name;
        $map['status']  =   1;
        $config  =   M('Addons')->where($map)->getField('config');
        if($config){
            $config   =   json_decode($config, true);
        }else{
        	return false;
        }
        $_config[$name]     =   $config;
        return $config;
    }
}
?>