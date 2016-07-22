<?php
namespace Admin\Controller;

/**
 * 升级规则控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class UpgroupController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){
        
    	$list = $this->lists("upgroup");
    	$this->assign("list",$list);
        $this->meta_title = '升级规则';
        $this->display();
    }
    
    public function add(){
    	
    	$group_list = M('auth_group')->where('status=1')->select();
    	$this->assign("group_list",$group_list);
    	$this->meta_title="添加规则";
    	$this->display("edit");
    	
    }
    
    public function edit($id){
    	
    	$where['id'] = $id;
    	$info = M('upgroup')->where($where)->find();
    	$this->assign("info",$info);
    	
    	$group_list = M('auth_group')->where('status=1')->select();
    	$this->assign("group_list",$group_list);
    	$this->meta_title="编辑规则";
    	$this->display("edit");
    }
    
    public function update(){
    	
    	if(IS_POST){
    		$data = I('post.');
    		if(empty($data['id'])){
    			
    			$id = M('upgroup')->add($data);
    			if($id){
    				$this->success("添加规则成功！",U('index'));
    			}else{
    				$this->error("添加失败！",U('index'));
    			}
    			
    		}else{
    			
    			$num = M('upgroup')->save($data);
    			if($num){
    				$this->success("修改规则成功！",U('index'));
    			}else{
    				$this->error("修改规则失败！",U('index'));
    			}
    		}
    		
    	}
    	
    }

}
