<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 运费控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class FreightController extends AdminController {

    public function index(){
    	$list = $this->lists("freight");
    	$this->assign("list",$list);
        $this->meta_title = '运费管理';
        $this->display();
    }

    /**
     * 新增菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function add(){
    	
    	if(IS_POST){
    		$data = I('post.');
    		$id = M('freight')->add($data);
    		if($id){
    			$this->success("添加成功！",U("index"));
    		}else{
    			$this->error("添加失败！",U("index"));
    		}
    	}else{
    		$this->meta_title = '新增规则';
    		$this->display('edit');
    	}
    	
        
    }

    /**
     * 编辑配置
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $data = I('post.');
            $res = M('freight')->save($data);
            if($res!== false ){
            	$this->success('更新成功', U("index"));
            } else {
               $this->error('更新失败', U("index"));
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('freight')->field(true)->find($id);
            $this->assign('info', $info);
            $this->meta_title = '编辑运费规则';
            $this->display();
        }
    }

    /**
     * 删除后台菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('freight')->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    public function toogleHide($id,$value = 1){
        $this->editRow('Menu', array('hide'=>$value), array('id'=>$id));
    }

    public function toogleDev($id,$value = 1){
        $this->editRow('Menu', array('is_dev'=>$value), array('id'=>$id));
    }

    public function importFile($tree = null, $pid=0){
        if($tree == null){
            $file = APP_PATH."Admin/Conf/Menu.php";
            $tree = require_once($file);
        }
        $menuModel = D('Menu');
        foreach ($tree as $value) {
            $add_pid = $menuModel->add(
                array(
                    'title'=>$value['title'],
                    'url'=>$value['url'],
                    'pid'=>$pid,
                    'hide'=>isset($value['hide'])? (int)$value['hide'] : 0,
                    'tip'=>isset($value['tip'])? $value['tip'] : '',
                    'group'=>$value['group'],
                )
            );
            if($value['operator']){
                $this->import($value['operator'], $add_pid);
            }
        }
    }

    public function import(){
        if(IS_POST){
            $tree = I('post.tree');
            $lists = explode(PHP_EOL, $tree);
            $menuModel = M('Menu');
            if($lists == array()){
                $this->error('请按格式填写批量导入的菜单，至少一个菜单');
            }else{
                $pid = I('post.pid');
                foreach ($lists as $key => $value) {
                    $record = explode('|', $value);
                    if(count($record) == 2){
                        $menuModel->add(array(
                            'title'=>$record[0],
                            'url'=>$record[1],
                            'pid'=>$pid,
                            'sort'=>0,
                            'hide'=>0,
                            'tip'=>'',
                            'is_dev'=>0,
                            'group'=>'',
                        ));
                    }
                }
                $this->success('导入成功',U('index?pid='.$pid));
            }
        }else{
            $this->meta_title = '批量导入后台菜单';
            $pid = (int)I('get.pid');
            $this->assign('pid', $pid);
            $data = M('Menu')->where("id={$pid}")->field(true)->find();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 菜单排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M('Menu')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '菜单排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M('Menu')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->eorror('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }
}
