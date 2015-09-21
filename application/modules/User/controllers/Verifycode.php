<?php
/**
 * verify code controller class
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-2-26
 */
class VerifycodeController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$telephone = TZ_Request::checkTelephone();
		$verifyCode = TZ_Request::checkVerifyCode();
		
		$validStatus = TZ_Loader::service('VerifyCode','User')->valid($telephone, $verifyCode);
		TZ_Request::success(array(array('verified' => $validStatus)));
	}
}