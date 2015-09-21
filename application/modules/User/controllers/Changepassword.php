<?php
/**
 * Change password controller class
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-26
 */
class ChangepasswordController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$sessionId = TZ_Request::checkSessionId();
		$password = TZ_Request::checkPassword();
		$oldPassword = TZ_Request::checkOldPassword();
		
		$changeStatus = TZ_Loader::service('User','User')->changePassword($sessionId, $oldPassword, $password);
		if (!$changeStatus)
			throw new Exception('修改密码失败。');
		
		TZ_Request::success();
	}
}