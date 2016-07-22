<?php

namespace Addons\Piaofu;
use Common\Controller\Addon;

/**
 * Qq漂浮插件
 * @author 智网天下
 */

    class PiaofuAddon extends Addon{

        public $info = array(
            'name'=>'Piaofu',
            'title'=>'Qq漂浮',
            'description'=>'显示在网页上的QQ漂浮',
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
        	
        	$qq = explode("\r\n",$config['QQ']);
        	$this->assign('addons_qq',$qq);
        	
        	$tel = explode("\r\n",$config['tel']);
        	$this->assign('addons_tel',$tel);
        	
        	$fourzz = explode("\r\n",$config['fourzz']);
        	$this->assign('addons_fourzz',$fourzz);
        	
        	$this->assign('addons_config', $config);
        	
        	if($config['place']){
        		$tpl = "style/".$config['style']."/index";
        		$this->display($tpl);
        	}
        }

    }