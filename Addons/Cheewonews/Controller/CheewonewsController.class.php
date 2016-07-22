<?php

namespace Addons\Cheewonews\Controller;
use Home\Controller\AddonsController;

class CheewonewsController extends AddonsController{

	public function get_news(){
    	
		$url = "http://www.cheewo.com/Article/getCheewoNews.html";
		$list = file_get_contents($url);
		$list = json_decode($list,true);
		$str = "";
		for($i=0;$i<count($list);$i++){
			$str .= "<li>";
			$str .= "<em><a target='_blank' href='". U("Home/Article/detail@www.cheewo.com","id=".$list[$i]['id']) ."'>" . $list[$i]['title'] . "</a></em>";
			$str .= "<span>". time_format($list[$i]['update_time']) ."</span>";
			$str .= "</li>";
		}
		$this->success('成功', '', array('data'=>$str));
	}
}