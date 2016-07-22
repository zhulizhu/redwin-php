<?php
namespace Manager\Model;
use Think\Model;

class WechatMaterialModel extends Model {

    # 自动验证规则
    protected $_validate = array(
		array('content','require','文本内容必须填写'),
		array('fileid','require','请上传或选择文件'),
		//array('Title','require','请上传或选择文件'),
		//array('Description','require','请上传或选择文件'),
    );

    # 自动完成规则
    protected $_auto = array(
        //array('status', 0, self::MODEL_INSERT, 'string'),
        array('addtime', NOW_TIME, self::MODEL_INSERT),
    );

}
