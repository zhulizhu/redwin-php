<?php
namespace Wechat\Controller;

use OT\DataDictionary;
use Admin\Controller\PublicController;
use Common\Controller\QRcode;
use Common\Controller\word;
use Common\Controller\Wechat;

/**
 * 前台短信控制器
 */
class SmsController extends HomeController {
	
	public function index($pass){
		if($pass!="cheewo"){
			die("die!!!");
		}
		
		$where = array();
		$where['mobile'] = "18628965391";
		
		$list = M("ucenter_member")->where($where)->field("id,mobile")->select();
		for($i=0;$i<count($list);$i++){
			
			$res = $this->zhiyan_sms($list[$i]['mobile'],"IAIJWASDJASD");
			
			dump($res);
			exit;
			
		}
		
		
	}
	
	
	/**
	 * 发送短信
	 *
	 * @param number $mobile
	 * @param string $content
	 */
	public function zhiyan_sms($mobile, $tpl) {
		// 智验apiKey
		$apiKey = "5ab6c721698e4d46be0710dd55c134da";
		// 应用appId
		$appId = "i8Dou9n99426";
		// 应用绑定模板ID
		$templateId = $tpl;
		// 手机号
		// 参数
		$param = "1234";
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
		$result = json_decode ( $result, true );
		return $result;
	}
	
	
}