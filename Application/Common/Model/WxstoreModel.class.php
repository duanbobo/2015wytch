<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Model;
use Think\Model;

class WxstoreModel extends Model{
	
	protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
        array('createtime', NOW_TIME, self::MODEL_INSERT),
        array('updatetime', NOW_TIME, self::MODEL_BOTH),
	);
	
}
