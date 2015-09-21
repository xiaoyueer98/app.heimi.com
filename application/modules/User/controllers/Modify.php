<?php
/**
 * ModifyController class file
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-26
 */
class ModifyController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$sessionId = TZ_Request::checkSessionId();
		$name = TZ_Request::checkName();
		$gender = TZ_Request::checkGender();
		$params = TZ_Request::getParams('post');
		
		$uid = TZ_Loader::service('SessionManager','User')->getUid($sessionId);
		if (!$uid)
			throw new Exception('请先登陆。');
		
		$userInfo['name'] = $name;
		$userInfo['gender'] = $gender;
		
		if (isset($params['area']))
			$userInfo['area'] = TZ_Request::clean($params['area']);
		if (isset($params['city']))
			$userInfo['city'] = TZ_Request::clean($params['city']);
		
		$updateStatus = TZ_Loader::service('User','User')->updateInfo($uid, $userInfo);
		if (!$updateStatus)
			throw new Exception('更新用户信息失败。');
		
		TZ_Request::success();
	}
}