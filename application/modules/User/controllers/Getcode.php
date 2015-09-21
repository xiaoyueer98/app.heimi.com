<?php
/**
 * 获取验证码
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-21
 */
class GetcodeController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$telephone = TZ_Request::checkTelephone();
		$reset = TZ_Request::checkVerifyMode();

		$service = TZ_Loader::service('VerifyCode','User');
		if ($reset == '1')
			$verifyCode = $service->createRegisterCode($telephone);
		else 
			$verifyCode = $service->createResetCode($telephone);
		TZ_Request::success(array(array('verify_code' => '')));
	}
}

