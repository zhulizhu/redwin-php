<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Wechat',
    'MODULE_DENY_LIST'   => array('Common', 'User'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin','Wechat'),
		
	/* 动态载入类库 */
	"LOAD_EXT_FILE"=>"emoji",

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'oOb,D;m`S^tKe9LQ3f&NMGUvVYuP2h*@g[JF?4{X', //默认数据加密KEY


	'SHOW_PAGE_TRACE' => false,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID


    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
	'DB_CHARSET'=> 'utf8mb4',
	'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'redwin', // 数据库名
    'DB_USER'   => 'redwin', // 用户名
    'DB_PWD'    => '123456789',  // 密码

	
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'cw_', // 数据库表前缀

    /* 文档模型配置 (文档模型核心配置，请勿更改) */
    'DOCUMENT_MODEL_TYPE' => array(2 => '主题', 1 => '目录', 3 => '段落'),
    
    /*支付宝配置参数*/
    'alipay_config'=>array(
    		'partner' =>'2088911700904265',   //这里是你在成功申请支付宝接口后获取到的PID；
    		'key'=>'mk8h8ibbyco9gvf3yka7o88wvsvfyrj0',//这里是你在成功申请支付宝接口后获取到的Key
    		'sign_type'=>strtoupper('MD5'),
    		'input_charset'=> strtolower('utf-8'),
    		'cacert'=> getcwd().'\\cacert.pem',
    		'transport'=> 'http',
    ),
    
    'alipay'   =>array(
    		//这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
    		'seller_email'=>'99cmh@cheewo.com',
    		//这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
    		'notify_url'=>'http://www.99cmh.com/Pay/notifyurl',
    		//这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
    		'return_url'=>'http://www.99cmh.com/Pay/returnurl',
    		//支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
    		'successpage'=>'User/myorder?ordtype=payed',
    		//支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
    		'errorpage'=>'User/myorder?ordtype=unpay',
    ),
    
);
