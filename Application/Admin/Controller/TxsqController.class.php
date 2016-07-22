<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 分佣详情控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class TxsqController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){
    	
    	$where = array();
    	
    	if (isset ( $_GET ['uid'] )) {
    		$where ['uid'] = array (
    				'like',
    				'%' . $_GET ['uid'] . '%'
    		);
    	}
    	if(I('time-start') && I('time-end') ){
    		$where['add_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
    	}
    	
    	$where['money_type'] = 2;
    	$list = $this->lists("MoneyLog",$where);
    	$this->assign("list",$list);
    	
    	$money = M('MoneyLog')->where($where)->getField("money",true);
    	$money = array_sum($money);
    	session('where',$where);
    	$this->assign("money",$money);
    	$this->meta_title = '提现记录';
    	$this->display();
    }
    
    /**
     * 导出预约列表
     * @param string $method
     * @author 智网天下科技 http://www.cheewo.com
     * @作者：安彦飞
     */
    public function exportRadio($outId=null){
        $order=M("MoneyLog")->where(session('where'))->order('id desc')->select();
        for($i=0;$i<count($order);$i++){
            $doc_info[$i]['user'] = get_nickname($order[$i]['uid']);
            
        }
        for($k=0;$k<count($order);$k++){
            $order[$k]['username'] = $doc_info[$k]['user'];
        }
        $file_name =date('Y-m-d H:i:s',time())." 提现记录";
        header('Content-Type: text/xls');
        header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
        $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
        header('Content-Disposition: attachment;filename="' .$str . '.xls"');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
    
        $table_data = "<table style='font-size:14px;' border=1px;>";
        $table_data .="<tr style='font-size:16px;'>";
        $table_data .='<td style="background:yellow;">ID</td>';
        $table_data .='<td style="background:yellow;">用户</td>';
        $table_data .='<td style="background:yellow;">金额</td>';
        $table_data .='<td style="background:yellow;">操作时间</td>';
        $table_data .="</tr>";
        for($i=0;$i<count($order);$i++){
            $table_data .="<tr>";
            $table_data .="<td>".$order[$i]["id"]."</td>";
            $table_data .="<td>".$order[$i]["username"]."</td>";
            $table_data .="<td>".$order[$i]["money"]."|"."</td>";
            $table_data .="<td>".time_format($order[$i]["add_time"])."</td>";
            $table_data .="</tr>";
        }
        $table_data.="</table>";
        echo $table_data;
        die();
    }

}
