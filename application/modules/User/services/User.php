<?php
/**
 * user service file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-24
 */
class UserService {
	const MEMBERSHIP_SCORE = 10;

	/**
	 * register
	 *
	 * @param string $telephone
	 * @param string $password
	 * @param string $name
	 * @return array
	 */
	public function register($telephone, $password, $name) {
		$usersModel = TZ_Loader::model('Users', 'User');
		//查询用户是否注册
		$result     = $usersModel->select(array('telephone:eq' => $telephone), '*', 'ROW');
		//print_r($result);
		if (!empty($result))
		{
			throw new Exception('用户已经注册。');
		}
		$idManager = TZ_Loader::service('IdManager', 'User');
		$db        = Yaf_Registry::get('user_db');
		$db->transBegin();
		//create user
		$userId                  = $idManager->createUID();
		$userInfo['id']          = $userId;
		$userInfo['telephone']   = $telephone;
		$userInfo['password']    = $password;
		$userInfo['name']        = $name;
		$userInfo['vip']         = 1;
		$userInfo['is_verified'] = 0;
		$userInfo['created_at']  = $currentTime             = date('Y-m-d H:i:s');
		$userInfo['updated_at']  = $currentTime;
		$userInfo['invite_code'] = '';
		$usersModel->insert($userInfo);
		if (false === $db->transStatus())
		{
			$db->rollback();
			throw new Exception('创建用户失败。');
		}
		$relationModel          = TZ_Loader::model('UniqueUsers', 'User');
		$uniqueId               = $idManager->createUid();
		$relation['id']         = $uniqueId;
		$relation['mapper_id']  = $userId;
		$relation['old_id']     = $userId;
		$relation['type']       = 1;
		$relation['created_at'] = $currentTime;
		$relation['updated_at'] = $currentTime;
		$relationModel->insert($relation);
		if (false === $db->transStatus())
		{
			$db->rollback();
			throw new Exception('创建用户关系失败。');
		}
		$accountInfo['id'] = TZ_Loader::service('IdManager', 'User')->createUid();
		$accountInfo['unique_user_id'] = $uniqueId;
		$accountInfo['score'] = 0;
		$accountInfo['status'] = 1;
		$accountInfo['created_at'] = $accountInfo['updated_at'] = date('Y-m-d H:i:s');
		$addScore =TZ_Loader::model('Account', 'User')->insert($accountInfo);
		if (false === $addScore)
		{
			$db->rollback();
			throw new Exception("error");
		}
		$db->commit();
		return TZ_Loader::service('SessionManager', 'User')->create($uniqueId);
	}

	/**
	 * login
	 *
	 * @param string $telephone
	 * @param string $password
	 * @return string
	 */
	public function login($telephone, $password) {

		$userInfo = TZ_Loader::model('Users', 'User')->getInfoByTelephone($telephone);

		if (empty($userInfo))
		throw new Exception('用户不存在，请先注册。');
		if (strtoupper($userInfo['password']) != strtoupper($password))
		throw new Exception('密码错误，请重试。');
		return TZ_Loader::service('SessionManager', 'User')->create($userInfo['uid']);
	}

	/**
	 * logout
	 *
	 * @param string $sessionId
	 * @return boolean
	 */
	public function logout($sessionId) {
		return TZ_Loader::service('SessionManager', 'User')->discard($sessionId);
	}

	/**
	 * change password
	 *
	 * @param string $sessionId
	 * @param string $oldPassword
	 * @param string $password
	 * @return boolean
	 */
	public function changePassword($sessionId, $oldPassword, $password) {
		$uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
		if (!$uid)
		throw new Exception('您还没有登陆，无法执行此操作。');

		$userModel = TZ_Loader::model('Users', 'User');
		$userInfo  = $userModel->getInfoByUid($uid);
		if (strtoupper($userInfo['password']) != strtoupper($oldPassword))
		throw new Exception('密码错误，请重试。');

		$set['password'] = $password;
		return $userModel->updateInfoByUid($uid, $set);
	}

	/**
	 * reset password
	 *
	 * @param string $telephone
	 * @param string $password
	 * @return boolean
	 */
	public function resetPassword($telephone, $password) {
		$userModel = TZ_Loader::model('Users', 'User');
		$userInfo  = $userModel->getInfoByTelephone($telephone);
		if (empty($userInfo))
		throw new Exception('用户不存在，无法执行此操作。');

		$set['password'] = $password;
		return $userModel->updateInfoByUid($userInfo['uid'], $set);
	}

	/**
	 * get user info
	 *
	 * @param string $uid
	 * @param array $fields
	 * @return array
	 */
	public function getInfoByUid($uid, $fields = array()) {
		return TZ_Loader::model('Users', 'User')->getInfoByUid($uid, $fields);
	}

	/**
	 * get user info by telephone
	 *
	 * @param string $telephone
	 * @param array $fields
	 * @return array
	 */
	public function getInfoByTelephone($telephone, $fields = array()) {
		return TZ_Loader::model('Users', 'User')->getInfoByTelephone($telephone, $fields);
	}

	/**
	 * update user info
	 *
	 * @param string $uid
	 * @param string $userInfo
	 * @return array
	 */
	public function updateInfo($uid, $userInfo) {
		return TZ_Loader::model('Users', 'User')->updateInfoByUid($uid, $userInfo);
	}

	/**
	 * delete user info
	 *
	 * @param string $telephone
	 * @return int
	 */
	public function deleteInfo($telephone) {
		return TZ_Loader::model('Users', 'User')->deleteInfoByTelephone($telephone);
	}

	/**
	 * logout
	 *
	 * @param string $sessionId
	 * @return boolean
	 */
	public function validTelphone($telephone) {
		$condition                 = array();
		$condition['telephone:eq'] = $telephone;
		$set['is_verified']        = 1;
		$set['updated_at']         = date('Y-m-d H:i:s');
		TZ_Loader::model('Users', 'User')->update($set, $condition);
		return TZ_Loader::model('Users', 'User')->delCache($telephone);
	}
}
