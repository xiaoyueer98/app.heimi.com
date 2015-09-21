<?php
/**
 * ResetPassword controller class
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-26
 */
class ResetpasswordController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$telephone = TZ_Request::checkTelephone();
		$verifyCode = TZ_Request::checkVerifyCode();
		$password = TZ_Request::checkPassword();
		
		//valid
		$validStatus = TZ_Loader::service('VerifyCode','User')->valid($telephone, $verifyCode);
		if (!$validStatus)
			throw new Exception('验证码错误。');
		
		$resetStatus = TZ_Loader::service('User','User')->resetPassword($telephone, $password);
		if (!$resetStatus)
			throw new Exception('重置密码失败。');
		
		//unset
		TZ_Loader::service('VerifyCode','User')->discard($telephone);
		
		TZ_Request::success();
	}
}