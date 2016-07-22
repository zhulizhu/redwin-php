<?php
// +----------------------------------------------------------------------
// | CheeWoPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.cheewo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lihao <lihao@cheewo.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;


/**
 * SEO工具
 * @author lihao <lihao@cheewo.com>
 */
class SeoController extends ManagerController {
	
	
    /**
     * 应用商城
     * @author lihao <lihao@cheewo.com>
     */
    public function index(){
    	
    	$this->meta_title = '应用商城';
    	$this->display();
    }
    
    public function seo(){

    	$this->display();
    }
    
    public function Grab(){
    	$keyword = C('WEB_SITE_KEYWORD');
    	$keyword = implode(",",$keyword);
    	$this->assign("keyword",$keyword);
    	
    	//分类
    	$tree = D('Category')->getTree(0,'id,name,title,sort,pid,model,allow_publish,status');
    	$this->assign('tree', $tree);
    	
    	$this->display();
    }
    
    
    public function startGrab(){
    	echo "请稍后，系统正在为您抓取数据！";
    	$data = I('post.');
    	$id = I('post.id');
    	for($i=0;$i<count($id);$i++){//循环分类
    		$keyword = $data['keyword'.$id[$i]];
    		$keyword = explode(",",$keyword);
    		$num = $data['num'.$id[$i]];
    		$model_id = $data['model_id'.$id[$i]];
    		for($j=0;$j<count($keyword);$j++){//循环关键词
    			$newnum = $num/10;
    			for($k=0;$k<$newnum;$k++){//循环次数
    				$url = "http://news.baidu.com/ns?word=".urlencode($keyword[$j])."&pn=".$k."0";
    				$content = file_get_contents($url);//获取该页面搜索出来的内容
    				/*获取标题*/
    				$p='#<h3 class="c-title">(.+?)</h3>#s';
    				preg_match_all($p,$content,$tit);
    				/*获取标题*/
    				foreach ($tit[1] as $val){
    					$document['name'] = "";
    					$document['level'] = 0;
    					$document['title'] = noHtml($val).C('WEB_SITE_TITLE');
    					$document['description'] = $document['title'];
    					$document['category_id'] = $id[$i];
    					$document['model_id'] = $model_id;
    					$document['type'] = 2;
    					$document['pid'] = 0;
    					$document['deadline'] = 0;
    					$document['create_time'] = time();
    					$document['root'] = 0;
    					$document['position'] = 0;
    					$document['link_id'] = 0;
    					$document['cover_id'] = 0;
    					$document['display'] = 1;
    					$document['attach'] = 0;
    					$document['view'] = 0;
    					$document['comment'] = 0;
    					$document['extend'] = 0;
    					$document['update_time'] = time();
    					$document['status'] = 1;
    					$document['uid'] = is_login();
    					D('Document')->autoAdd($document);
    				}
    			}
    		}
    	}
    	
    }
    
    
    
    public function get_Ranking($keyword,$page = 1){
    	static $px = 0;
    	$rsState = false;
    	
    	$url = C('SYS_DOMAIN');
    
    	$enKeyword = urlencode($keyword);
    	$firstRow = ($page - 1) * 10;
    
    	if($page > 10){
    		return "关键词'".$keyword."在'100页之内没有该网站排名..";
    	}
    	$contents = file_get_contents("http://www.baidu.com/s?wd=$enKeyword&&pn=$firstRow");
    	preg_match_all('/<table[^>]*?class="result"[^>]*>[\s\S]*?<\/table>/i',$contents,$rs);
    	foreach($rs[0] as $k=>$v){
    		$px++;
    		if(strstr($v,$url)){
    			$rsState = true;
    			preg_match_all('/<h3[\s\S]*?(<a[\s\S]*?<\/a>)/',$v,$rs_t);
    			$str .= '当前 "' . $url . '" 在百度关键字 "' . $keyword . '" 中的排名为：' . $px;
    			$str .= '<br>';
    			$str .= '第' . $page . '页;第' . ++$k . "个<a target='_blank' href='http://www.baidu.com/s?wd=$enKeyword&&pn=$firstRow'>进入百度</a>";
    			$str .= '<br>';
    			$str .= $rs_t[1][0];
    			return $str;
    		}
    	}
    	unset($contents);
    	if($rsState === false){
    		$this->get_Ranking($keyword,++$page);
    	}
    }
        
}
