<?php

namespace Addons\Cheewonews;
use Common\Controller\Addon;

/**
 * 智网新闻插件
 * @author 智网天下科技
 */

    class CheewonewsAddon extends Addon{

        public $info = array(
            'name'=>'Cheewonews',
            'title'=>'智网新闻',
            'description'=>'将显示智网天下科技有限公司最新新闻，以及版本升级信息',
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
        

        //实现的AdminIndex钩子方法
        public function AdminIndex($param){
        	$config = $this->getConfig();
        	
        	$this->assign("info",$this->info);
        	$this->assign('addons_config', $config);
        	if($config['display']){
        		$this->display('widget');
        	}
        }

    }