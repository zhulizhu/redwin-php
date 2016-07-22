<?php

namespace Addons\Jiathis;
use Common\Controller\Addon;

/**
 * 侧栏式分享插件
 * @author 智网天下
 */

    class JiathisAddon extends Addon{

        public $info = array(
            'name'=>'Jiathis',
            'title'=>'侧栏式分享',
            'description'=>'显示在网页上的侧栏式分享',
            'status'=>1,
            'author'=>'智网天下',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的pageFooter钩子方法
        public function pageFooter($param){
			
			
			$config = $this->getConfig();
			if($config['display']){
				$str = '<script type="text/javascript" src="http://v3.jiathis.com/code/jiathis_r.js?';
				
				$str .= 'type='.$config['display'];//显示位置
				
				if($config['piaofu']==0){//固定漂浮
					$str .= '&amp;move=0';
				}
				
				if($config['style']){//按钮样式
					$str .= '&amp;btn='.$config['style'].'.gif';
				}
				
				$str .= '" charset="utf-8"></script>';
				echo $str;
			}
			
			
        }

    }