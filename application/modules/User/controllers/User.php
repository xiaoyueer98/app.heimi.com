<?php
/**
 * login controller class
 *
 *
 * @author octopus <zhangguipo@747.cn>
 * @final 2014-10-20
 */
class UserController extends Yaf_Controller_Abstract
{
	//run service
	public function indexAction()
	{
		 $data=TZ_Loader::service('Blacklist','Flow')->getList();
		 TZ_Request::success($data);
	}
}
