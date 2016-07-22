<?php
namespace Admin\Model;
use Think\Model;

class WechatDkhModel extends Model {

    # 自动验证规则
    protected $_validate = array(
        array('nickname', 'require', '昵称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    /*# 自动完成规则
    protected $_auto = array(
        array('status', 0, self::MODEL_INSERT, 'string'),
        array('create_time', 'time', self::MODEL_BOTH, 'function'),
    );*/
	
	public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }
        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
		}
    	return $res;
    }

}
