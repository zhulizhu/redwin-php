<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Manager\Controller;
use User\Api\UserApi;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class UserController extends ManagerController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $nickname       =   I('nickname');
        $map['status']  =   array('egt',0);
        $map['uid'] = array('gt',1);//排除网站创始人
        
        
        
        if(is_numeric($nickname)){
            $map['uid|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
        }
        
        if(isset($_GET['group'])){//分组查询
        	$group_id = I('get.group');
        	$thislist = M('AuthGroupAccess')->where('group_id='.$group_id)->getField('uid',true);
        	$thislist = implode(',',$thislist);
        	$map['uid'] = array('in',$thislist);
        	$list = $this->lists('Member',$map);
        	$this->assign('thisgroup',I('get.group'));
        }else{
        	$list   = $this->lists('Member', $map);
        }
        
        int_to_string($list);
        for($i=0;$i<count($list);$i++){
        	$group = M('AuthGroupAccess')->where('uid='.$list[$i]['uid'])->find();
        	$group = M('AuthGroup')->where('id='.$group['group_id'])->find();
        	$group = $group['title'];
        	if(empty($group)){
        		$group = "未分组";
        	}
        	$list[$i]['group'] = $group;
        }
        
        /*获取分组信息*/
        $grouplist = M('AuthGroup')->select();
        $this->assign('grouplist',$grouplist);
        
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display();
    }
    
    /**
     * 编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
    	$id = I('get.id','');
    	if(empty($id)){
    		$this->error('参数不能为空！');
    	}
    	//用户表单模型信息
    	$model = M('Usermodel')->where('id=2')->find();
    	$this->assign('model',$model);
    	//用户表单字段信息
    	$fields = $this->get_userfiled($model['id']);
    	$this->assign('fields',$fields);
    	//获取用户信息
    	/* 调用用户接口 */
    	$User   =   new UserApi;
    	$data   =   $User->infoall($id);
    	$data = array_merge($data,M('member')->where('uid='.$id)->find());
    	$this->assign('data',$data);
    	
    	//获取用户分组
    	$auth_group = M('AuthGroup')->where('status=1')->select();
    	$thisgroup = M('AuthGroupAccess')->where('uid='.$id)->find();
    	$thisgroup = $thisgroup['group_id'];
    	$this->assign('auth_group',$auth_group);
    	$this->assign('thisgroup',$thisgroup);
    	
    	
    	$this->meta_title = '编辑用户';
    	$this->display();
    }
    
    /**
     * 更新用户数据
     */
    public function update(){
    	
    	//获取参数
    	$UID = I('id');//用户ID
    	$data['nickname'] = I('nickname');//用户名
    	empty($data['nickname']) && $this->error('请输入用户名');
    	$group = I('auth_group');//用户组
    	empty($group) && $this->error('请选择用户组');
    	$data['mobile'] = I('mobile');//手机
    	$data['email'] = I('email');//邮箱
    	
    	//更新基础数据
    	$Api    =   new UserApi();
    	$res    =   $Api->update($UID , $data);
    	if(!$res['status']){
    		$this->error('更新基础信息出错！');		
    	}
    	//更新分组
    	$thisgroup = M('AuthGroupAccess')->where('uid='.$UID)->find();
    	$Auth_group['group_id'] = $group;
    	$Auth_group['uid'] = $UID;
    	if(empty($thisgroup)){
    		$result = M('AuthGroupAccess')->add($Auth_group);
    		if(!$result){
    			$this->error('添加分组失败！');
    		}
    	}else{
    		$result = M('AuthGroupAccess')->where('uid='.$UID)->save($Auth_group);
    		if($result===false){
    			$this->error('更新分组失败');
    		}
    	}
    	//更新扩展数据
    	$member = D('Member');
    	$result = $member->update(I('post.'));
    	if($result !== false ){
    		$this->success("更新成功");
    	}
    	
    	
    }

    /**
     * 修改昵称初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updateNickname(){
        $nickname = M('Member')->getFieldByUid(UID, 'nickname');
        $this->assign('nickname', $nickname);
        $this->meta_title = '修改昵称';
        $this->display();
    }

    /**
     * 修改昵称提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitNickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error('请输入昵称');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data   =   $Member->create(array('nickname'=>$nickname));
        if(!$data){
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改昵称成功！');
        }else{
            $this->error('修改昵称失败！');
        }
    }

    /**
     * 修改密码初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display();
    }

    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitPassword(){
        //获取参数
        $password   =   I('post.old');
        empty($password) && $this->error('请输入原密码');
        $data['password'] = I('post.password');
        empty($data['password']) && $this->error('请输入新密码');
        $repassword = I('post.repassword');
        empty($repassword) && $this->error('请输入确认密码');

        if($data['password'] !== $repassword){
            $this->error('您输入的新密码与确认密码不一致');
        }

        $Api    =   new UserApi();
        $res    =   $Api->updateInfo(UID, $password, $data);
        if($res['status']){
            $this->success('修改密码成功！');
        }else{
            $this->error($res['info']);
        }
    }

    /**
     * 用户行为列表
     * @author huajie <banhuajie@163.com>
     */
    public function action(){
        //获取列表数据
        $Action =   M('Action')->where(array('status'=>array('gt',-1)));
        $list   =   $this->lists($Action);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->assign('_list', $list);
        $this->meta_title = '用户行为';
        $this->display();
    }

    /**
     * 新增行为
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增行为';
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑行为
     * @author huajie <banhuajie@163.com>
     */
    public function editAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑行为';
        $this->display();
    }

    /**
     * 更新行为
     * @author huajie <banhuajie@163.com>
     */
    public function saveAction(){
        $res = D('Action')->update();
        if(!$res){
            $this->error(D('Action')->getError());
        }else{
            $this->success($res['id']?'更新成功！':'新增成功！', Cookie('__forward__'));
        }
    }
    

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('Member', $map );
                break;
            case 'resumeuser':
                $this->resume('Member', $map );
                break;
            case 'deleteuser':
                $this->delete('Member', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function add($username = '', $password = '', $repassword = '', $email = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('Member')->add($user)){
                    $this->error('用户添加失败！');
                } else {
                    $this->success('用户添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->meta_title = '新增用户';
            $this->display();
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }
    
    /**
     * 获取属性信息并缓存
     * @param  integer $id    属性ID
     * @param  string  $field 要获取的字段名
     * @return string         属性信息
     */
    public function get_userfiled($model_id,$group = true){    

    /* 非法ID */
    if(empty($model_id) || !is_numeric($model_id)){
        return '';
    }

    /* 获取属性 */
    if(!isset($list[$model_id])){
        $map = array('model_id'=>$model_id);
        $extend = M('Usermodel')->getFieldById($model_id,'extend');

        if($extend){
            $map = array('model_id'=> array("in", array($model_id, $extend)));
        }
        $info = M('Userfiled')->where($map)->select();
        $list[$model_id] = $info;
        //S('attribute_list', $list); //更新缓存
    }

    $attr = array();
    foreach ($list[$model_id] as $value) {
        $attr[$value['id']] = $value;
    }

    if($group){
        $sort  = M('Usermodel')->getFieldById($model_id,'field_sort');

        if(empty($sort)){	//未排序
            $group = array(1=>array_merge($attr));
        }else{
            $group = json_decode($sort, true);
            $keys  = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if(!empty($attr)){
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        $attr = $group;
    }
    return $attr;
    }

}
