<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Addons\EditorForAdmin;
use Common\Controller\Addon;

/**
 * 编辑器插件
 * @author yangweijie <yangweijiester@gmail.com>
 */

	class EditorForAdminAddon extends Addon{

		public $info = array(
			'name'=>'EditorForAdmin',
			'title'=>'后台编辑器',
			'description'=>'用于增强整站长文本的输入和显示',
			'status'=>1,
			'author'=>'thinkphp',
			'version'=>'0.1'
		);

		public function install(){
			return true;
		}

		public function uninstall(){
			return true;
		}

		/**
		 * 编辑器挂载的后台文档模型文章内容钩子
		 * @param array('name'=>'表单name','value'=>'表单对应的值')
		 */
		public function adminArticleEdit($data){
			$this->assign('addons_data', $data);
			$congif = $this->getConfig();
			if(!empty($data['height'])){//设置高度
				$congif['editor_height'] = $data['height'];
			}
			if(!empty($data['editor_type'])){//设置编辑器类型
				$congif['editor_type'] = $data['editor_type'];
			}
			$this->assign('addons_config', $congif);
			$this->display('content');
		}
	}
