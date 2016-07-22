<?php
// +----------------------------------------------------------------------
// | CheeWoPHP   成都智网天下科技有限公司
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Home\Controller;

class BrandController extends HomeController {
	/* 文档模型频道页 */
	public function index(){
		$id = $_GET['id'];
		if(!$id){
			$ids=M('Purcate')->find();
			$id=$ids['id'];
		}
		$area = M('Linkage')->where('pid=3193')->select();
		$purcate = D('Purcate')->info($id,false);
		$purcateAll = M('Purcate')->getField('id,title,pid');
		$map['category_id']=$id;
		$map['pid']=0;
		$map['status']=1;
		$purchase = M('purchase')->where($map)->select();
		for($j=0;$j<count($purchase);$j++){
			$purcate['cars'] .= $purchase[$j]['title']." ";
			$cars.=$purchase[$j]['id'].",";
		}
		$list = D('Purchase')->positions($id,1);
		/*获取系列图片*/
		if ($purcate["review"]!=""){
			$where["id"][]="in";
			$where["id"][]=$purcate["review"];
			$result = M('picture')->where($where)->limit(4)->select();
			for($i=0; $i<count($result);$i++){
				$result[$i]["photo"]=picture($result[$i]["id"]);
			}
		}
		
		$map1['cars'] = array('in',$cars);
		$map1['status'] = array('neq',-1);
		$apply = M('Apply')->where($map1)->order('id desc')->getField('id,cars,create_time,username');
		$this->assign('sertime',NOW_TIME);
		$this->assign('review', $result);//团购回顾
 		$this->assign('list',$list);//团购产品
		$this->assign('purcateAll',$purcateAll);//所有品牌信息
		$this->assign('purcate',$purcate);//该品牌信息
		$this->assign('area',$area);//报名地区
		$this->assign('apply',$apply);//报名信息
		$this->assign('applyNum',count($apply));//报名人数
		$this->display();
	}
	public function brandindex(){
		$area = M('Linkage')->where('pid=3193')->select();
		$purcate = D('Purcate')->getTree();
		for($i=0;$i<count($purcate);$i++){
			$map['category_id']=$purcate[$i]['id'];
			$map['pid']=0;
			$map['status']=1;
			$purchase = M('purchase')->where($map)->select();
			for($j=0;$j<count($purchase);$j++){
				$purcate[$i]['number'] += $purchase[$j]['number'];
				$purcate[$i]['cars'] .= $purchase[$j]['title']." ";
			}
		}
		$list = D('Purchase')->positions(0,2);
		$this->assign('plist',$list);//团购车型
		$this->assign('purcate',$purcate);//栏目
		$this->assign('area',$area);//报名地区
		$this->display();
	}
	//车系联动
	public function linktwo($id){
		$map['category_id']=$id;
		$map['pid']=0;
		$map['status']=1;
		$cars=  M('Purchase')->where($map)->select();
		if(count($cars)>0){
			echo("[");
			$j=1;
			for($i=0;$i<count($cars);$i++){
				echo("{id:".$cars[$i]["id"].",title:'".$cars[$i]["title"]."'}");
				if($j<>count($cars)){
					echo(",");
				}
				$j=$j+1;
			}
			echo("]");
		}
	}
	//配置联动
	public function linkThree($id){
		$map['pid']=$id;
		$map['type']=2;
		$map['status']=1;
		$deploy=  M('Purchase')->where($map)->select();
		if(count($deploy)>0){
			echo("[");
			$j=1;
			for($i=0;$i<count($deploy);$i++){
				echo("{id:".$deploy[$i]["id"].",title:'".$deploy[$i]["title"]."'}");
				if($j<>count($deploy)){
					echo(",");
				}
				$j=$j+1;
			}
			echo("]");
		}
	}
	//修改团购时间
	public function setTime($id=0,$time=null){
		$id = $_GET['id'];
		$time =$_GET['time'];
		
		if($id&&$time){
			M('Purcate')->where('id='.$id)->setField('time',$time);
			exit();
		}
	}
}