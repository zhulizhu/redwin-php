<?php
namespace Manager\Model;
use Think\Model;

class WechatConfigModel extends Model {

    # 自动验证规则
    protected $_validate = array(
		array('wechatid','require','原始ID必须填写'),
		array('token','require','Token必须填写'),
    );

    /*# 自动完成规则
    protected $_auto = array(
        array('status', 0, self::MODEL_INSERT, 'string'),
        array('create_time', 'time', self::MODEL_BOTH, 'function'),
    );*/

}
