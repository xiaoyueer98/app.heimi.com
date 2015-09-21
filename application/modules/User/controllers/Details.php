<?php
/**
 * DetailsController class
 * 
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-26
 */
class DetailsController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$params = TZ_Request::getParams('post');
		if (isset($params['user_id'])) {
			
			if (empty($params['user_id']) || !is_numeric($params['user_id']))
				throw new Exception('user_id不能为空。');
			$uid = $params['user_id'];
			
			$fields = array('name', 'icon', 'type', 'area', 'city', 'vip', 'gender','is_verified','invite_code');
			$userInfo = TZ_Loader::service('User','User')->getInfoByUid($uid, $fields);
			if (empty($userInfo))
				throw new Exception('用户不存在。');
			
			$userInfo['user_type'] = $userInfo['type'];
			unset($userInfo['type']);
			
		} elseif (isset($params['session_id'])) {
			
			if (empty($params['session_id']))
				throw new Exception('session_id不能为空。');
			
			$uid = TZ_Loader::service('SessionManager','User')->getUid($params['session_id']);
			if (!$uid)
				throw new Exception('请先登陆。');
			
			$fields = array('uid', 'telephone', 'name', 'icon', 'type', 'area', 'city', 'vip', 'gender','is_verified','invite_code');
			$userInfo = TZ_Loader::service('User','User')->getInfoByUid($uid, $fields);
			if (empty($userInfo))
				throw new Exception('用户不存在。');
				
			$userInfo['user_type'] = $userInfo['type'];
			unset($userInfo['type']);
			$userInfo['id'] = $userInfo['uid'];
			unset($userInfo['uid']);
			
		} else {
			throw new Exception('参数不能为空。');
		}
		//print_r($userInfo);
		//format
		$userInfo['user_type'] = intval($userInfo['user_type']);
		$userInfo['gender'] = intval($userInfo['gender']);
		$userInfo['vip'] = intval($userInfo['vip']);
		TZ_Request::success(array($userInfo));
	}
}