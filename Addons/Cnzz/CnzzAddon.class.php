<?php

namespace Addons\Cnzz;
use Common\Controller\Addon;

/**
 * 网站统计插件
 * @author 智网天下科技
 */

    class CnzzAddon extends Addon{

        public $info = array(
            'name'=>'Cnzz',
            'title'=>'网站统计',
            'description'=>'基于Cnzz的网站信息统计',
            'status'=>1,
            'author'=>'智网天下科技',
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
        	echo $config['id'];
        }

    }