<?php
// +----------------------------------------------------------------------
// | CheeWoPHP   成都智网天下科技有限公司
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
class ApplyController extends HomeController {
	
	public function index(){
		if(IS_POST){
			$result = D('Apply')->update();
			if($result){
				$content ='团购报名：恭喜您成功报名车盟汇“一台也团购”，30天内凭此短信到指定经销商处立享当期团购价，参与夺宝环节满99元，更可获一千元购车抵用券【99车盟汇】';
				sendsms ( $result['mobile'], $content );
				echo "恭喜团购报名成功！";
			}else {
				echo "报名失败！";
			}
		}
	}
	
}