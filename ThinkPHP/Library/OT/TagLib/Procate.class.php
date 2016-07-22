<?php
namespace OT\TagLib;
use Think\Template\TagLib;
/**
 * 分类标签
 */
class Procate extends TagLib{
	/**
	 * 定义标签列表
	 * @var array
	 */
	protected $tags   =  array(
		'list'     => array('attr' => 'name,pid,child,row,field,tpl', 'close' => 1), //获取指定分类列表
		'sanji'     => array('attr' => 'name,pid,child,row,field,tpl', 'close' => 1), //获取指定分类列表
	);

	public function _list($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		$pid    = empty($tag['pid']) ? '0' : $tag['pid'] ;
		$child  = empty($tag['child']) ? 'false' : $tag['child'];
		$row    = empty($tag['row'])   ? '10' : $tag['row'];
		$field  = empty($tag['field']) ? true : $tag['field'];
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		$parse  = '<?php ';
		$parse .= '$__LIST__ = D("Procate")->getTree('.$pid.',true);';
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
	
	public function _sanji($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		$pid    = empty($tag['pid']) ? '$category[\'id\']' : $tag['pid'] ;
		$child  = empty($tag['child']) ? 'false' : $tag['child'];
		$row    = empty($tag['row'])   ? '10' : $tag['row'];
		$field  = empty($tag['field']) ? true : $tag['field'];
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		$parse  = '<?php ';
		$parse .= '$__LIST__ = D("Procate")->getTree('.$pid.',true);';
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

	
}