<?php
/**
 * 用户卡表
 * @author  尼克<zhaozhiwei@747.cn>
 * @final 2015-01-26
 */
class UserCardModel extends TZ_Db_Table
{	
	public function __construct()
	{
		parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.user_card');
	}
}