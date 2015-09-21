<?php
/**
 * Logout controller class
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-2-26
 */
class LogoutController extends Yaf_Controller_Abstract
{
	//exec service
	public function indexAction()
	{
		$sessionId = TZ_Request::checkSessionId();
		
		$logoutStatus = TZ_Loader::service('User','User')->logout($sessionId);
		/*if (!$logoutStatus)
			throw new Exception('注销登陆失败。');
		*/
		TZ_Request::success();
	}
}
