<?php
/**
 * register controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-25
 */
class ValidateController extends Yaf_Controller_Abstract
{
	//run service
	public function indexAction()
	{
		$sessionId = TZ_Request::checkSessionId();
		$uid = TZ_Loader::service('SessionManager','User')->getUid($sessionId);

		if (!$uid)
			throw new Exception('请先登陆。');
	
		$verifyCode = TZ_Request::checkVerifyCode();
		$userInfo = TZ_Loader::service('User', 'User')->getInfoByUid($uid);
		
		if (empty($userInfo) || !is_array($userInfo))
			throw new Exception('请先登陆。');
		//valid
		$telephone=$userInfo['telephone'];
		$validStatus = TZ_Loader::service('VerifyCode','User')->valid($telephone, $verifyCode);
		if (!$validStatus)
			throw new Exception('验证码错误。');
		//unset
		TZ_Loader::service('VerifyCode','User')->discard($telephone);
		//更新验证字段	
		TZ_Loader::service('User', 'User')->validTelphone($telephone);
		//response
		TZ_Request::success(array(array('session_id' => $sessionId)));
	}
}
