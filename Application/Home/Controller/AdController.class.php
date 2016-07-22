<?php
// +----------------------------------------------------------------------
// | CheeWoPHP   成都智网天下科技有限公司
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Home\Controller;

class AdController extends HomeController {
	
	/* 广告 */
	public function index($pid=null){
		header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header("content-type: image/png");
		$img = __ROOT__."/Public/cheewo/images/img40.jpg";
		echo $img;
	}
	
	/**
	 * 还不错
	 */
	public function a(){
		
	}
	
}