<?php
/**
 * account model class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-04-17
 */
class AccountModel extends TZ_Db_Table
{

	public function __construct()
	{
		parent::__construct(Yaf_Registry::get('user_db'), 'user_db.account');
	}

}
