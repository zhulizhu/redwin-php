<?php
namespace OT\TagLib;
use Think\Template\TagLib;
/**
 * ThinkCMS 系文档模型标签库
 */
class Sql extends TagLib{
	/**
	 * 定义标签列表
	 * @var array
	 */
	protected $tags   =  array(
		'list'     => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取指定分类列表
		'CateBrand'     => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取指定分类列表
		'info'  => array('attr' => 'name,category,child,page,row,field', 'close' => 1), //获取一条文章信息
		'page'     => array('attr' => 'cate,listrow', 'close' => 0), //列表分页
	);

	public function _list($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		$model  = empty($tag['model']) ? false : $tag['model'];
		$where  = empty($tag['where']) ? '' : $tag['where'];
		$row    = empty($tag['row'])   ? '10' : $tag['row'];
		$field  = empty($tag['field']) ? 'true' : $tag['field'];
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
		
		if(!$model) return false;//空表不执行
		$parse  = '<?php ';
		$parse .= '$model=M("'.$model.'");';
		$parse .= '$__LIST__ = $model->where('.$where.')->select();';
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
	
	public function _CateBrand($tag, $content){
		$name   = empty($tag['name']) ? 'list' : $tag['name'];
		$model  = empty($tag['model']) ? false : $tag['model'];
		$class  = empty($tag['class']) ? '' : $tag['class'];
		$row    = empty($tag['row'])   ? '10' : $tag['row'];
		$field  = empty($tag['field']) ? 'true' : $tag['field'];
		$tpl    = empty($tag['tpl']) ? false : $tag['tpl'];
	
		if(!$model) return false;//空表不执行
		$parse  = '<?php ';
		$parse .= '$model=M("'.$model.'");';
		$parse .= '$map["class"]=array("like","%'.$class.'%");';
		$parse .= '$__LIST__ = $model->where($map)->select();';
		$parse .= ' ?>';
		$parse .= '<volist name="__LIST__" id="'. $name .'">';
		if($tpl){
			$parse .= '<include name="taglib/'.$tpl.'"/>';
		}else{
			$parse .= $content;
		}
		$parse .= '</volist>';
		dump($parse);
		exit();
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
	
	/**
	 * 读取一条记录
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
	
}