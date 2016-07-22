<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Manager\Controller;
use Admin\Model\AuthGroupModel;

/**
 * 产品参数控制器
 * @author huajie <banhuajie@163.com>
 */
class ParametersController extends ManagerController {

    /**
     * 联动菜单首页
     * @author huajie <banhuajie@163.com>
     */
    public function index($pid = 0){
    	$map['status'] = array('gt',-1);
        $map['pid'] = $pid;
        $list = $this->lists('Parameters',$map);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        if($pid!=0){
        	$data = D('Parameters')->info($pid);
        	$this->assign('sort',$data);
        }
        $this->assign('pid',$pid);
        $this->assign('_list', $list);
        $this->meta_title = '联动菜单列表';
        $this->display();
    }

    /**
     * 新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add($pid=0){
        //获取所有的模型
        if(IS_POST){
			
        	if(!D('Parameters')->update()){
        		$this->error('添加失败');
        	}else{
        		$this->success('添加成功',U('Parameters/index?pid='.$pid));
        	}
        }else{
        	if($pid!=0){
        		$info = D("Parameters")->info($pid);
        	}
        	unset($info['id']);
        	$info['title'] = "";
        	$info['pid'] = $pid;
        	$sort = M('procate')->where(array("pid"=>0))->select();
        	$this->assign('sort',$sort);
        	$this->assign('info',$info);
			$this->assign('pid',$pid);
        	$this->meta_title = '新增产品参数';
        	$this->display('edit');
        }
    }

    /** 
     * 编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
    	if(IS_POST){
    		if(!D('Parameters')->update()){
    			$this->error('修改失败');
    		}else{
    			$this->success('修改成功',U('Parameters/index?pid='.$pid));
    		}
    	}else{
    		$id = $_REQUEST['id'];
    		if(empty($id)){
    			$this->error('参数不能为空！');
    		}
    		/*获取一条记录的详细数据*/
    		$Model = M('Parameters');
    		$data = $Model->field(true)->find($id);
    		if(!$data){
    			$this->error($Model->getError());
    		}
    		$sort = M('procate')->where(array("pid"=>0))->select();
        	$this->assign('sort',$sort);
    		$this->assign('fields', $fields);
    		$this->assign('info', $data);
    		$this->meta_title = '编辑产品参数';
    		$this->display();
    	}
        
    }

    /**
     * 删除一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function del(){
        $ids = I('get.ids');
        empty($ids) && $this->error('参数不能为空！');
        $ids = explode(',', $ids);
        foreach ($ids as $value){
            $res = M('Linkage')->delete($value);
            if(!$res){
                break;
            }
        }
        if(!$res){
            $this->error('删除失败!');
        }else{
            $this->success('删除成功！');
        }
    }
    
    public function make($id){
    	$Linkage = D('Linkage');
    	$info = $Linkage->info($id);
    	$list = $Linkage->where(array('pid='.$id))->select();
    	if(count($list)>0){
    		$str ="<select class='".$info['name']."' name='".$info['name']."[]'>";
    		for($i=0;$i<count($list);$i++){
    			$str .= "<option>".$list[$i]['title']."</option>";
    		}
    		$str .= "</select>";
    		echo $str;
    	}
    	
    }
}
