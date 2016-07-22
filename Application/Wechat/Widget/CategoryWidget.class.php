<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Widget;
use Think\Action;

/**
 * 分类widget
 * 用于动态调用分类信息
 */

class CategoryWidget extends Action{
	
	/* 显示指定分类的同级分类或子分类列表 */
	public function lists($cate, $child = false,$Current){
		$model = D('Category');
		$Document = D('Document');
		$field = 'id,name,pid,title,link_id,model';
		if($child){
			$category = $model->getTree($cate, $field);
		} else {
			$Subclasses=$model->info($cate,$child);
			if ($Subclasses["pid"]!=0) {
				$pd=1;
				$category = $model->getSameLevel($cate, $field);
			}else
			{
				$pd=2;
				$category = $Document->lists($cate);
			}
		}
		/*获取当前分类的顶级分类信息，作为当前位置*/
		$topcategory = $model->getTopId($cate);
		$this->assign('topcategory', $topcategory);//当前分类信息
		$this->assign('category', $this->ShowList( $model->getSortUrl( $category ) ,$cate,$Current,$pd));//当前分类子级
		
		$this->assign('current', $cate);//当前分类ID(以判断焦点)
		$this->display('Category/lists');
	}
	
	//循环递归左侧导航
	 private function ShowList($dataList,$cate,$Current,$pd){
		$value = "";
		if (count ( $dataList ) > 0) {
			$value .= "<ul>\r\n";
			foreach ( $dataList as $row ) {
				
				if ($pd==1) {
					if ( $row ['id']  == $cate) {
						$value .= "<li class=\"active\">";
					} else {
						$value .= "<li>";
					}
				}else
				{
					if ( $row ['id']  == $Current) {
						$value .= "<li class=\"active\">";
					} else {
						$value .= "<li>";
					}
				}
				$value .= "<a href=\"$row[linkurl]\">$row[title]</a>";
				
				if (array_key_exists ( 'list', $row ) && count ( $row ['list'] ) > 0) {
					$value .= $this->ShowList($row['list']);
				}
				
				$value .= "</li>\r\n";
			}
			$value .= "</ul>\r\n";
		}
		return $value;
	}
	
}
