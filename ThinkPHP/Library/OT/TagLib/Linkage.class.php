<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace OT\TagLib;
use Think\Template\TagLib;
/**
 * 联动菜单标签
 */
class Linkage extends TagLib{
	/**
	 * 定义标签列表
	 * @var array
	 */
	protected $tags   =  array(
		'partlist' => array('attr' => 'id,field,page,name', 'close' => 1), //段落列表
		'partpage' => array('attr' => 'id,listrow', 'close' => 0), //段落分页
		'prev'     => array('attr' => 'name,info', 'close' => 1), //获取上一篇文章信息
		'next'     => array('attr' => 'name,info', 'close' => 1), //获取下一篇文章信息
		'page'     => array('attr' => 'cate,listrow', 'close' => 0), //列表分页
		'position' => array('attr' => 'pos,cate,limit,filed,name', 'close' => 1), //获取推荐位列表
		'list'     => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取指定分类列表
		'lists'     => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取指定分类列表
		'Address'  => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取指定分类列表
		'info'  => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取一条文章信息
	);

	public function _list($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		$cate   = empty($tag['typeid']) ? '$category[\'id\']' : $tag['typeid'] ;
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		$parse  = '<?php ';
		$parse .= '$__LIST__ = D("Linkage")->lists('.$cate.');';
		$parse .= ' ?>';
		$parse .= '<volist name="__LIST__" id="'. $name .'">';
		if($tpl){
			$parse .= '<include name="taglib/'.$tpl.'"/>';
		}else{
			$parse .= $content;
		}
		$parse .= '</volist>';
		return $parse;
	}
	
	public function _lists($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		if($tag['typeid']==null){
			$cate='$category[\'id\']';
		}else{
			$cate=$tag['typeid'];
		}
		//$cate   = empty($tag['typeid']) ? '$category[\'id\']' : $tag['typeid'] ;
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		$parse  = '<?php ';
		$parse .= '$__LIST__ = D("Linkage")->listse('.$cate.');';
		$parse .= ' ?>';
		$parse .= '<volist name="__LIST__" id="'. $name .'">';
		if($tpl){
			$parse .= '<include name="taglib/'.$tpl.'"/>';
		}else{
			$parse .= $content;
		}
		$parse .= '</volist>';
		return $parse;
	}
	
	/**
	 * 读取一篇文章
	 * @param unknown $tag
	 * @param unknown $content
	 * @return string
	 */
	public function _info($tag, $content){
		$id   = empty($tag['id']) ? '$category[\'id\']' : $tag['id'] ;
		$parse  = '<?php ';
		$parse .= '$Document = D(\'Document\');';
		$parse .= '$info = $Document->detail('.$id.');';
		$parse .= ' ?>';
		$parse .= $content;
		return $parse;
	}
	
	/**
	 * volist标签解析 循环输出数据集
	 * 格式：
	 * <volist name="userList" id="user" empty="" >
	 * {user.username}
	 * {user.email}
	 * </volist>
	 * @access public
	 * @param array $tag 标签属性
	 * @param string $content  标签内容
	 * @return string|void
	 */
	public function _Address($tag,$content) {
		$name   = empty($tag['name']) ? "NowAddress" : $tag['name'];
		$id     = empty($tag['id']) ? 'adds' : $tag['id'];
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		$parse .= '<volist name="'. $name .'" id="'. $id .'">';
		if($tpl){
			$parse .= '<extend name="taglib/'.$tpl.'" />';
		}else{
			$parse .= $content;
		}
		$parse .= '</volist>';

		return $parse;
	}

	/* 推荐位列表 */
	public function _position($tag, $content){
		$pos    = $tag['pos'];
		$cate   = $tag['cate'];
		$limit  = empty($tag['limit']) ? 'null' : $tag['limit'];
		$field  = empty($tag['field']) ? 'true' : $tag['field'];
		$name   = $tag['name'];
		$parse  = '<?php ';
		$parse .= '$__POSLIST__ = D(\'Document\')->position(';
		$parse .= $pos . ',';
		$parse .= $cate . ',';
		$parse .= $limit . ',';
		$parse .= $field . ');';
		$parse .= ' ?>';
		$parse .= '<volist name="__POSLIST__" id="'. $name .'">';
		$parse .= $content;
		$parse .= '</volist>';
		return $parse;
	}

	/* 列表数据分页 */
	public function _page($tag){
		$cate    = $tag['cate'];
		$listrow = $tag['listrow'];
		$parse   = '<?php ';
		$parse  .= '$__PAGE__ = new \Think\Page(get_list_count(' . $cate . '), ' . $listrow . ');';
		$parse  .= 'echo $__PAGE__->show();';
		$parse  .= ' ?>';
		return $parse;
	}

	/* 获取下一篇文章信息 */
	public function _next($tag, $content){
		$name   = $tag['name'];
		$info   = $tag['info'];
		$parse  = '<?php ';
		$parse .= '$' . $name . ' = D(\'Document\')->next($' . $info . ');';
		$parse .= ' ?>';
		$parse .= '<notempty name="' . $name . '">';
		$parse .= $content;
		$parse .= '</notempty>';
		return $parse;
	}

	/* 获取上一篇文章信息 */
	public function _prev($tag, $content){
		$name   = $tag['name'];
		$info   = $tag['info'];
		$parse  = '<?php ';
		$parse .= '$' . $name . ' = D(\'Document\')->prev($' . $info . ');';
		$parse .= ' ?>';
		$parse .= '<notempty name="' . $name . '">';
		$parse .= $content;
		$parse .= '</notempty>';
		return $parse;
	}

	/* 段落数据分页 */
	public function _partpage($tag){
		$id      = $tag['id'];
		if ( isset($tag['listrow']) ) {
			$listrow = $tag['listrow'];
		}else{
			$listrow = 10;
		}
		$parse   = '<?php ';
		$parse  .= '$__PAGE__ = new \Think\Page(get_part_count(' . $id . '), ' . $listrow . ');';
		$parse  .= 'echo $__PAGE__->show();';
		$parse  .= ' ?>';
		return $parse;
	}

	/* 段落列表 */
	public function _partlist($tag, $content){
		$id     = $tag['id'];
		$field  = $tag['field'];
		$name   = $tag['name'];
		if ( isset($tag['listrow']) ) {
			$listrow = $tag['listrow'];
		}else{
			$listrow = 10;
		}
		$parse  = '<?php ';
		$parse .= '$__PARTLIST__ = D(\'Document\')->part(' . $id . ',  !empty($_GET["p"])?$_GET["p"]:1, \'' . $field . '\','. $listrow .');';
		$parse .= ' ?>';
		$parse .= '<?php $page=(!empty($_GET["p"])?$_GET["p"]:1)-1; ?>';
		$parse .= '<volist name="__PARTLIST__" id="'. $name .'">';
		$parse .= $content;
		$parse .= '</volist>';
		return $parse;
	}
}