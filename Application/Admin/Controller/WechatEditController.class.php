<?php
namespace Admin\Controller;
use Common\Controller\Wechat;

class WechatEditController extends WechatController {
	
	public function config(){
		$id = I('request.id');
		$info = M('wechat_'.ACTION_NAME)->find($id);
		$this->edit($info, '编辑系统参数');
	}
	
	public function material(){
		if(IS_POST){
			$post = I('post.');
			$type = I('post.type');
			unset($post['id'],$post['type'],$post['fileid']);
			if($type == Wechat::MSGTYPE_VIDEO || $type == Wechat::MSGTYPE_MUSIC){
				$data = $post;
			}
			else if($type == Wechat::MSGTYPE_NEWS){
				foreach($post as $key=>$val){
					foreach($val as $k=>$v){
						$data[$k][$key] = $v;
						/*$data[] = array('Title' => $v, 'Description' => $desc[$k], 'PicUrl' => $image[$k], 'Url' => $url[$k],);*/
					}
				}
			}
			isset($data) && $_POST['content'] = Wechat::tojson($data);
            $Table = D('wechat_'.ACTION_NAME);
            $data = $Table->create();
            if($data){
                if($Table->save()!== false){
                    $this->success('更新成功', __MODULE__.'/Wechat/material/type/'.$type.'.html');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Table->getError());
            }
		}
		else{
			$id = I('request.id');
			$materialType = C('materialType');
			$info = M('wechat_'.ACTION_NAME.' as m ')->field("m.*, f.name as filename, f.savename, f.savepath")->join("LEFT JOIN tb_wechat_file AS f ON m.fileid=f.id where m.id='$id'")->find();
			$data = json_decode($info['content'],true);
			if($info['type'] == 'news')
				foreach($data[0] as $key=>$val){
					$info[$key] = $val;
				}
			else
				foreach($data as $key=>$val){
					$info[$key] = $val;
				}
			$info['path'] = __ROOT__.'/uploads/download/'.$info['savepath'].$info['savename'];
			$this->type = $info['type'];
			$this->edit($info, '编材'.$materialType[$this->type]);
		}
	}
	
    /**
     * 编辑
     */
    private function edit($info='', $title='', $model=ACTION_NAME){
        if(IS_POST){
            $Table = D('wechat_'.$model);
            $data = $Table->create();
            if($data){
                if($Table->save()!== false){
                    $this->success('更新成功', str_replace(CONTROLLER_NAME, 'Wechat', __SELF__));
				} else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Table->getError());
            }
        } else {
            if(false === $info){
                $this->error('获取编辑内容信息错误');
            }
            $this->info=$info;
            $this->meta_title = $title;
            $this->ACTION_NAME = ACTION_NAME;
			$this->display('Wechat:edit_'.$model);
        }
    }
}
//echo '<br />'.__ROOT__.'<br />'.__APP__.'<br />'.__MODULE__.'<br />'.__CONTROLLER__.'<br />'.__ACTION__.'<br />'.__SELF__;