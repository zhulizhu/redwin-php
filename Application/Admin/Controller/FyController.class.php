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
class FyController extends AdminController {

    /**
     * 后台菜单首页
     * @return none
     */
public function index(){
    	
    	if (isset ( $_GET ['uid'] )) {
    		$where ['uid'] = array (
    				'like',
    				'%' . $_GET ['uid'] . '%'
    		);
    	}
    	
    	if (isset ( $_GET ['search_order'] )) {
    		
    		$where['order_id'] = $_GET['search_order'];
    		
    	}
    	
    	if(I('time-start') && I('time-end') ){
    		$where['add_time'] = array("between",strtotime(I('time-start')).",".strtotime(I('time-end')));
    	}
    	
    	if(isset($_GET['money_type'])){
    		$where['money_type'] = I('money_type');
    	}
    	session('where',$where);
    	$list = $this->lists("MoneyLog",$where);
    	$this->assign("list",$list);
    	$moneylist = M('MoneyLog')->where($where)->field("money,money_type")->select();
    	$money = 0;
    	$xsmoney=0;
    	$xxmoney=0;
    	$xfmoney = 0;
    	//1线上收入 3退款回收
    	for($i=0;$i<count($moneylist);$i++){
    		if($moneylist[$i]['money_type']==1 || $moneylist[$i]['money_type']==0){
    			$money = $money + $moneylist[$i]['money'];
    		}else{
    			$money = $money - $moneylist[$i]['money'];
    		}
    	}
    	$this->assign("money",$money);
    	
    	for($j=0;$j<count($moneylist);$j++){
    	if($moneylist[$j]['money_type']==1){
    			$xsmoney = $xsmoney + $moneylist[$j]['money'];
    		}elseif ($moneylist[$j]['money_type']>=2){
    			$xfmoney = $xfmoney + $moneylist[$j]['money'];
    		}else{
    			$xxmoney = $xxmoney + $moneylist[$j]['money'];
    		}
    	}
    	
		$this->assign("xfmoney",$xfmoney);
    	$this->assign("xsmoney",$xsmoney);
    	$this->assign("xxmoney",$xxmoney);
    	
    	$this->meta_title = '分佣详情';
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
//             $doc_info[$i]['user'] = M('UcenterMember')->where('id='.$order[$i]['uid'])->getField('username');
            $doc_info[$i]['user'] = get_nickname($order[$i]['uid']);
//             $comeuser[$i]['comeuser'] = M('UcenterMember')->where('id='.$order[$i]['puid'])->getField('username');
            $comeuser[$i]['comeuser'] = get_nickname($order[$i]['puid']);
        }
        for($k=0;$k<count($order);$k++){
            $order[$k]['username'] = $doc_info[$k]['user'];
            $order[$k]['comeuser'] = $comeuser[$k]['comeuser'];
        }
        $file_name =date('Y-m-d H:i:s',time())." 分佣详情";
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
        $table_data .='<td style="background:yellow;">类型</td>';
        $table_data .='<td style="background:yellow;">订单ID</td>';
        $table_data .='<td style="background:yellow;">来源用户</td>';
        $table_data .='<td style="background:yellow;">消费时间</td>';
        $table_data .="</tr>";
        for($i=0;$i<count($order);$i++){
            $table_data .="<tr>";
            $table_data .="<td>".$order[$i]["id"]."</td>";
            $table_data .="<td>".$order[$i]["username"]."</td>";
            $table_data .="<td>".$order[$i]["money"]."</td>";
            if($order[$i]["money_type"] == 0){
                $table_data .="<td>"."线下收入"."</td>";
            }
            if($order[$i]["money_type"] == 1){
                $table_data .="<td>"."线上收入"."</td>";
            }
            if($order[$i]["money_type"] == 2){
                $table_data .="<td>"."消费"."</td>";
            }
            if($order[$i]["money_type"] == 3){
                $table_data .="<td>"."退款回收"."</td>";
            }
            if($order[$i]["money_type"] == 4){
                $table_data .="<td>"."线下扣款"."</td>";
            }
            if($order[$i]["money_type"] == null){
                $table_data .="<td>"."所有"."</td>";
            }
            $table_data .="<td>".$order[$i]["order_id"]."</td>";
            $table_data .="<td>".$order[$i]["comeuser"]."</td>";
            $table_data .="<td>".time_format($order[$i]["add_time"])."</td>";
            $table_data .="</tr>";
        }
        $table_data.="</table>";
        echo $table_data;
        die();
    }
    

}
