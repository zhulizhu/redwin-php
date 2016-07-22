<?php
/**
 *	微信公众平台PHP-SDK, 官方API部分
 *  @author  dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.1
 *  usage:
 *   $options = array(
 *			'token'=>'tokenaccesskey' //填写你设定的key
 *		);
 *	 $weObj = new Wechat($options);
 *   $weObj->valid();
 *   $type = $weObj->getRev()->getRevType();
 *   switch($type) {
 *   		case Wechat::MSGTYPE_TEXT:
 *   			$weObj->text("hello, I'm wechat")->reply();
 *   			exit;
 *   			break;
 *   		case Wechat::MSGTYPE_EVENT:
 *   			....
 *   			break;
 *   		case Wechat::MSGTYPE_IMAGE:
 *   			...
 *   			break;
 *   		default:
 *   			$weObj->text("help info")->reply();
 *   }
 */
namespace Common\Controller;

class Wechat {
	
	const MSGTYPE_TEXT = 'text'; // 接收和发送
	const MSGTYPE_IMAGE = 'image'; // 接收和发送
	const MSGTYPE_VOICE = 'voice'; // 接收和发送
	const MSGTYPE_VIDEO = 'video'; // 接收和发送
	const MSGTYPE_LOCATION = 'location'; // 接收
	const MSGTYPE_LINK = 'link'; // 接收
	const MSGTYPE_MUSIC = 'music'; // 发送
	const MSGTYPE_NEWS = 'news'; // 发送
	const MSGTYPE_EVENT = 'event'; // 接收事件推送
	const transfer_customer_service = 'transfer_customer_service'; // 接收和发送
	const REPLY_ADD = 'add'; // 被添加自动回复
	const REPLY_AUTO = 'auto'; // 消息自动回复
	const REPLY_KEY = 'key'; // 关键词自动回复
	private $wechatid;
	private $token;
	public $access_token;
	public $cfg;
	private $_msg;
	private $_funcflag = false;
	public $_receive;
	private $debug =  false;
	private $_logcallback;
	
	public function __construct($options)//__construct
	{
		// 根据不同的 "原始ID" 获取不同的 token、appID、appsecret、access_token 等信息
		$this->config_list();
		$wechatid = get_def_wechatid();
		$this->wechatid = $wechatid;
		$this->update_config($wechatid);
		// other
		$this->debug = isset($options['debug']) ? $options['debug'] : false;
		$this->_logcallback = isset($options['logcallback']) ? $options['logcallback'] : false;
		// 获取接收数据
		$this->getRev();
	}

	/**
	 * 获取 token 等配置信息
	 */
	public function config_list($where='status=1'){
		$cfg = M('WechatConfig')->where($where)->select();
		foreach($cfg as $val){
			$this->cfg[$val['wechatid']]=$val;
		}
	}

	/**
	 * 根据 wechatid 更新配置，包括: token 和 access_token
	 */
	public function update_config($wechatid){
		$this->wechatid = $wechatid ? $wechatid : false;
		$this->token = $this->cfg[$this->wechatid]['token'];
		$this->access_token = $this->cfg[$wechatid]['access_token'] ? $this->cfg[$wechatid]['access_token'] : false;
	}
	
	/**
	 * For weixin server validation 
	 */	
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * For weixin server validation 
	 * @param bool $return 是否返回
	 */
	public function valid($return=false)
    {
        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        if ($return) {
        		if ($echoStr) {
        			if ($this->checkSignature()) 
        				return $echoStr;
        			else
        				return false;
        		} else 
        			return $this->checkSignature();
        } else {
	        	if ($echoStr) {
	        		if ($this->checkSignature())
	        			die($echoStr);
	        		else 
	        			die('no access');
	        	}  else {
	        		if ($this->checkSignature())
	        			return true;
	        		else
	        			die('no access');
	        	}
        }
        return false;
    }
    
	/**
	 * 设置发送消息
	 * @param array $msg 消息数组
	 * @param bool $append 是否在原消息数组追加
	 */
    public function Message($msg = '',$append = false){
    		if (is_null($msg)) {
    			$this->_msg =array();
    		}elseif (is_array($msg)) {
    			if ($append)
    				$this->_msg = array_merge($this->_msg,$msg);
    			else
    				$this->_msg = $msg;
    			return $this->_msg;
    		} else {
    			return $this->_msg;
    		}
    }
    
    public function setFuncFlag($flag) {
    		$this->_funcflag = $flag;
    		return $this;
    }
    
    private function log($log){
    		if ($this->debug && function_exists($this->_logcallback)) {
    			if (is_array($log)) $log = print_r($log,true);
    			return call_user_func($this->_logcallback,$log);
    		}
    }
    
    /**
     * 获取微信服务器发来的信息
     */
	public function getRev()
	{
		$postStr = file_get_contents("php://input");
		$this->log($postStr);
		if (!empty($postStr)) {
			$this->_receive = $this->xml_to_arr($postStr);
		}
		return $this;
	}
	
	/**
	 * 获取消息发送者
	 */
	public function getRevFrom() {
		if ($this->_receive)
			return $this->_receive['FromUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取消息接受者
	 */
	public function getRevTo() {
		if ($this->_receive)
			return $this->_receive['ToUserName'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息的类型
	 */
	public function getRevType() {
		if (isset($this->_receive['MsgType']))
			return $this->_receive['MsgType'];
		else 
			return false;
	}
	
	/**
	 * 获取消息ID
	 */
	public function getRevID() {
		if (isset($this->_receive['MsgId']))
			return $this->_receive['MsgId'];
		else 
			return false;
	}
	
	/**
	 * 获取消息发送时间
	 */
	public function getRevCtime() {
		if (isset($this->_receive['CreateTime']))
			return $this->_receive['CreateTime'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息内容正文
	 */
	public function getRevContent(){
		if (isset($this->_receive['Content']))
			return $this->_receive['Content'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息图片
	 */
	public function getRevPic(){
		if (isset($this->_receive['PicUrl']))
			return $this->_receive['PicUrl'];
		else 
			return false;
	}
	
	/**
	 * 获取接收消息链接
	 */
	public function getRevLink(){
		if (isset($this->_receive['Url'])){
			return array(
				'url'=>$this->_receive['Url'],
				'title'=>$this->_receive['Title'],
				'description'=>$this->_receive['Description']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收地理位置
	 */
	public function getRevGeo(){
		if (isset($this->_receive['Location_X'])){
			return array(
				'x'=>$this->_receive['Location_X'],
				'y'=>$this->_receive['Location_Y'],
				'scale'=>$this->_receive['Scale'],
				'label'=>$this->_receive['Label']
			);
		} else 
			return false;
	}
	
	/**
	 * 获取接收事件推送
	 */
	public function getRevEvent(){
		if (isset($this->_receive['Event'])){
			return array(
				'event'=>$this->_receive['Event'],
				'key'=>$this->_receive['EventKey'],
			);
		} else 
			return false;
	}
	
	public static function xmlSafeStr($str)
	{   
		if($str == "transfer_customer_service"){
			return '<![CDATA['.$str.']]>';
		}else{
			return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
		}
		
	}
	
	/**
	 * 数据Array编码
	 * @param mixed $xml 数据
	 * @return array
	 */
	public function xml_to_arr($xml) {
	    return (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	/**
	 * 数据JSON编码
	 * @param mixed $xml 数据
	 * @return json
	 */
	public function xml_to_json($xml) {
	    return json_encode($this->xml_to_arr($xml));
	}
	
	/**
	 * 数据XML编码
	 * @param mixed $data 数据
	 * @return string
	 */
	public static function data_to_xml($data) {
	    $xml = '';
	    foreach ($data as $key => $val) {
	        if(is_numeric($key)) $key = "item id=\"$key\"";
	        $xml    .=  "<$key>";
	        $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
	        list($key, ) = explode(' ', $key);
	        $xml    .=  "</$key>";
	    }
	    return $xml;
	}
	
	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $root 根节点名
	 * @param string $item 数字索引的子节点名
	 * @param string $attr 根节点属性
	 * @param string $id   数字索引子节点key转换的属性名
	 * @param string $encoding 数据编码
	 * @return string
	*/
	public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml   .= "<{$root}{$attr}>";
	    $xml   .= self::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}
	
	/**
	 * JSON编码
	 * @param mixed $data 数据
	 * @param string $encoding 数据编码
	 * @return string
	*/
	public static function urlcode($data){
		if(is_array($data)){
			foreach($data as $k=>$v){
				$data[$k] = self::urlcode($v);
			}
		}
		return is_array($data) ? $data : urlencode($data);
	}
	public static function tojson($data){
		return urldecode(json_encode(self::urlcode($data)));
	}
	
	/**
	 * 
	 * 回复微信服务器
	 * Example: $this->reply();
	 */
	public function reply($data, $msgtype)
	{
		$msg['ToUserName'] = $this->getRevFrom();
		$msg['FromUserName'] = $this->getRevTo();
		$msg['CreateTime'] = NOW_TIME;
		$msg['MsgType'] = $msgtype;
		switch($msgtype){
			case self::MSGTYPE_TEXT :$msg['Content'] = $data;break;
			case self::MSGTYPE_IMAGE:$msg['Image']['MediaId'] = $data;break;
			case self::MSGTYPE_VOICE:$msg['Voice']['MediaId'] = $data;break;
			case self::MSGTYPE_VIDEO:$msg['Video'] = json_decode($data,true);break;
			case self::MSGTYPE_MUSIC:$msg['Music'] = json_decode($data,true);break;
			case self::MSGTYPE_NEWS :
				//$data = json_decode($data,true);
				$msg['ArticleCount'] = count($data);
				$msg['Articles'] = json_decode($data,true);
				//foreach($data as $val){
				//	$msg['Articles']['item'] = $val;
				//}
			break;
		}
		echo $this->xml_encode($msg);
	}
	
	/**
	 * 发送客服消息
	 */
	public function send($touser, $msgtype, $data)
	{
		$msg['touser'] = $touser;
		$msg['msgtype'] = $msgtype;
		/*switch($msgtype){
			case self::MSGTYPE_TEXT  : $content['content']  = $data;break;
			case self::MSGTYPE_IMAGE : $content['media_id'] = $data;break;
			case self::MSGTYPE_VOICE : $content['media_id'] = $data;break;
			case self::MSGTYPE_VIDEO : $content = $data;break;
			case self::MSGTYPE_MUSIC : $content = $data;break;
			case self::MSGTYPE_NEWS  : $content['articles'][] = $data;break;
			default:$msg['msgtype']='text';$content['content']='开发中...';
		}*/
		$msgtype == self::MSGTYPE_NEWS ? $content['articles'][] = $data : $content = $data;
		$msg[$msgtype] = $content;
		//$msg[$msgtype] = $data;
		return $this->post("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=ACCESS_TOKEN", self::tojson($msg));
	}
	 
	/**
	 * 模拟请求
	 */
	public function get($url){
		$U = $this->auto_update($url); // 实时更新 access_token
		$context = stream_context_create(array(
			'http' => array (
			'method' => 'GET',
		)));
		$arr = $this->get_error(json_decode(file_get_contents($U, false, $context),true));
		if(($arr['errcode'] == 42001 || $arr['errcode'] == 40001)){
			$U = $this->uptoken($url);
			if($U){
				return $this->get2($U);
			}
		}
		return $arr;
	}
	public function get2($url){ // 用于自动更新 access_token 避免死循环
		$context = stream_context_create(array(
			'http' => array (
			'method' => 'GET',
		)));
		return $this->get_error(json_decode(file_get_contents($url, false, $context),true));
	}
	public function post($url,$data='', $upload=false){ // post需要php开启 openssl.dll
		$U = $this->auto_update($url); // 实时更新 access_token
		$context = stream_context_create(array(
			'http' => array (
			'method' => 'POST',
			'header' => "Content-type: ".($upload ? "multipart/form-data" : "application/x-www-form-urlencoded")."\r\n".
			"Content-Length: " . strlen($data) . "\r\n",
			'content' => $data,
		)));
		$arr = $this->get_error(json_decode(file_get_contents($U, false, $context),true));
		if(($arr['errcode'] == 42001 || $arr['errcode'] == 40001)){
			$U = $this->uptoken($url);
			if($U){
				return $this->post2($U, $data, $upload);
			}
		}
		return $arr;
	}
	public function post2($url,$data='', $upload=false){ // 用于自动更新 access_token 避免死循环  post需要php开启 openssl.dll
		$context = stream_context_create(array(
			'http' => array (
			'method' => 'POST',
			'header' => "Content-type: ".($upload ? "multipart/form-data" : "application/x-www-form-urlencoded")."\r\n".
			"Content-Length: " . strlen($data) . "\r\n",
			'content' => $data,
		)));
		return $this->get_error(json_decode(file_get_contents($url, false, $context),true));
	}
	public function request($url,$data=''){
		return $data=='' ? $this->get($url) : $this->post($url,$data);
	}
	
	/**
	 * 更新 access_token   PS: 因为 access_token 里面也调用的 get方法 所以不能直接放入 get方法 去判断
	 */
	public function uptoken($url){
		$cfg=$this->cfg[$this->wechatid]; // 根据不同的 "原始ID" 获取不同的 token、appID、appsecret、access_token 等信息
		$data = $this->get_access_token($cfg['appID'], $cfg['appsecret']); // 获取新的 access_token
		$data['expires_in'] += NOW_TIME; // 将微信给的7200秒加上当前时间
		M('wechat_config')->where("id='".$cfg['id']."'")->save($data); //存入新的 access_token 和 有效期
		$this->access_token = $data['access_token'];
		return str_replace('ACCESS_TOKEN', $this->access_token, $url);
	}
	
	/**
	 * 自动更新 access_token   PS: 因为 access_token 里面也调用的 get方法 所以不能直接放入 get方法 去判断
	 */
	public function auto_update($url){
		//$wechatid = get_def_wechatid();
		//$wechatid > 0 && $this->update_config($wechatid);
		$cfg=$this->cfg[$this->wechatid]; // 根据不同的 “原始ID” 获取不同的 token、appID、appsecret、access_token 等信息
		$cfg['expires_in'] < NOW_TIME && $this->uptoken(); // 如果 access_token 使用时间到期，更新 access_token
		//return false;
		return str_replace('ACCESS_TOKEN', $this->access_token, $url);
	}
	
	/**
	 * 发送模板消息
	 */
	public function tplmsg($openid,$template_id,$url,$data){
		$msg['touser'] = $openid;
		$msg['template_id'] = $template_id;
		$msg['url'] = $url;
		$msg['data'] = array();
		$temp = array();
		foreach($data as $key=> $val){
			$msg['data'][$key] = array("value"=>$val);
		}
		return $this->post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN",self::tojson($msg));
	}

	/**
	 * 获取新的 access_token
	 */
	public function get_access_token($appid, $secret){ // 使用 get2方法　避免死循环
		return $this->get2("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret");
	}
	
	/**
	 * 获取网页的 access_token
	 */
	public function get_web_access_token($appid, $secret,$code){ // 使用 get2方法　避免死循环
		return $this->get2("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code");
	}
	
	/**
	 * 获取网页用户的信息
	 */
	public function get_web_userinfo($access_token,$openid, $lang='zh_CN'){
		return $this->get("https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=$lang");
	}

	/**
	 * 上传文件
	 */
	public function upload($file, $fileType){ // 使用 get2方法　避免死循环
		$data = "--\r\n".
		"Content-Disposition: filename=\"".basename($file)."\"\r\n".
		"Content-Type:image\r\n\r\n".file_get_contents($file)."\r\n--\r\n";
		return $this->post("http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=$fileType", $data, $fileType);
	}

	/**
	 * 获取用户基本信息
	 */
	public function userinfo($openid, $lang='zh_CN'){
		return $this->get("https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=$openid&lang=$lang");
	}

	/**
	 * 获取关注者列表
	 */
	public function userlist($next_openid=1){
		if($next_openid==1){
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN";
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=$next_openid";
		}
		return $this->get($url);
	}

	/**
	 * 自定义菜单创建接口
	 */
	public function menu_create($menu){
		return $this->post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN", $menu);
	}

	/**
	 * 自定义菜单查询接口
	 */
	public function menulist(){
		return $this->get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=ACCESS_TOKEN");
	}

	/**
	 * 自定义菜单删除接口
	 */
	public function menu_delete(){
		return $this->get("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN");
	}
	
	/**
	 * 添加多客服
	 */
	public function add_dkf($data){
		return $this->post("https://api.weixin.qq.com/customservice/kfaccount/add?access_token=ACCESS_TOKEN",$data);
	}
	
	public function get_online_kf_list(){
		return $this->get("https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=ACCESS_TOKEN");
	}

	/**
	 * 获取错误信息
	 */
	public function get_error($data,$is_cn=true){ // {"errcode":44004,"errmsg":"empty content"}
		if($is_cn && is_numeric($data['errcode'])){
			$error_code = C('wechat_error_code');
			$data['errmsg'] = $error_code[$data['errcode']];
		}
		return $data;
	}
}