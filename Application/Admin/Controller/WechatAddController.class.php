<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

class WechatAddController extends WechatController {
	
	public function config(){
		$this->add('新增系统参数');
	}
	
	public function menu(){
		$this->add('新增自定义菜单');
	}
	
	public function material(){
		$type = trim(I('request.type','news'));
		$materialType = C('materialType');
		if(IS_POST){
			$post = I('post.');
			unset($post['id'],$post['type'],$post['fileid']);
			if($type == Wechat::MSGTYPE_VIDEO || $type == Wechat::MSGTYPE_MUSIC){
				$data = $post;
			}
			elseif($type == Wechat::MSGTYPE_NEWS){
				foreach($post as $key=>$val){
					foreach($val as $k=>$v){
						$data[$k][$key] = $v;
						/*$data[] = array('Title' => $v, 'Description' => $desc[$k], 'PicUrl' => $image[$k], 'Url' => $url[$k],);*/
					}
				}
			}
			isset($data) && $_POST['content'] = Wechat::tojson($data);
		}
		$this->type = $type;
		$this->add('新增'.$materialType[$type]);
	}
	
    /**
     * 新增
     */
    public function add($title, $model=ACTION_NAME){
        if(IS_POST){
            $Menu = D('wechat_'.$model);
            $data = $Menu->create();
            if($data){
                $id = $Menu->add();
                if($id){
                    $this->success('新增成功', str_replace(CONTROLLER_NAME, 'Wechat', __SELF__));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $this->meta_title = $title;
            $this->ACTION_NAME = ACTION_NAME;
			$this->display('Wechat:edit_'.$model);
        }
    }
}