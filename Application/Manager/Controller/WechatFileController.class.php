<?php
// +----------------------------------------------------------------------  PS: 比 FileController.class.php 多了 10 行的引用 Wechat
// | OneThink [ WE CAN DO IT JUST THINK IT ]									还有 32 - 61 行 的写入 WechatMediaId
// +---------------------------------------------------------------------- 
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +---------------------------------------------------------------------- 
namespace Manager\Controller;
use Common\Controller\Wechat;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class WechatFileController extends ManagerController {

    /* 文件上传 */
    public function upload(){
		$return  = array('status' => 1, 'info' => '上传成功');
		/* 调用文件上传组件上传文件 */
		$File = D('WechatFile');
		$file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
		$info = $File->upload(
			$_FILES,
			C('DOWNLOAD_UPLOAD'),
			C('DOWNLOAD_UPLOAD_DRIVER'),
			C("UPLOAD_{$file_driver}_CONFIG")
		);

        /* 记录附件信息、获取文件对应各个公众号的media_id */
        if($info){
            //$return['data'] = think_encrypt(json_encode($info['download']));
			$fileinfo = $info['download'];
			$return['id'] = $fileinfo['id'];
			$return['path'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/uploads/download/'.$fileinfo['savepath'].$fileinfo['savename'];
			/* 如果文件是第一次上传并且不是音乐和缩略图，就获取文件的 media_id */
			$filetype = $fileinfo['filetype'];
			if(!$fileinfo['repeat'] && !in_array($filetype,array('music','news'))){
           		$filepath = $return['path'];
				$a=0;
				$wechat = new Wechat; // 实例化 Wechat
				$config = $wechat->cfg; // 获取所有公众号
				foreach($config as $k=>$v){
					if(empty($v['access_token']))
						continue;
						$a++;
					$wechat->update_config($k); // 根据不同的 wechatid 使用不同的 access_token
					$response = $wechat->upload($filepath, $filetype); // 获取 media_id
					/* 如果成功获取 media_id ，就写入数据库 */
					if($response['type']){
						$data = $response; // 包含 type media_id created_at
						$data['expires_in'] = $data['created_at']+3600*24*3; // 获取到期时间 （ 创建时间 + 三天 ）
						$data['wechatid'] = $k; // 获取 wechatid
						$data['fileid'] = $fileinfo['id']; // 获取 fileid
						/* 写入数据库 */
						$WechatFileMediaId = M('WechatFileMediaId');
						$WechatFileMediaId->create($data);
						$WechatFileMediaId->add();
					}
					/* 接口调用频率超过限制 *
					else{
            			$return['status'] = 0;
            			$return['info']   = $response['errmsg'];
						continue;
					}/**/
				}
			}
		   /* 返回 media_id */
		   $map['wechatid'] = I('request.wechatid');
		   if($map['wechatid']){
			   $map['fileid'] = $return['id'];
			   $media = M('WechatFileMediaId')->where($map)->find();
			   $return['media_id']=$media['media_id'];
			   $return['fileid']=$return['id'];
			   $return['wechatid']=I('request.wechatid');
		   }
        } else {
            $return['status'] = 0;
            $return['info']   = $File->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    /* 下载文件 */
    public function download($id = null){
        if(empty($id) || !is_numeric($id)){
            $this->error('参数错误！');
        }

        $logic = D('Download', 'Logic');
        if(!$logic->download($id)){
            $this->error($logic->getError());
        }

    }

    /**
     * 上传图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture(){
        //TODO: 用户登录检测

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
}
