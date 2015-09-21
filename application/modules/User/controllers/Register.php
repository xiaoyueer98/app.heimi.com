<?php
/**
 * register controller class
 *
 * @author octopus <zhangguipo@747.cn>
 * @final 2014-10-27
 */
class RegisterController extends Yaf_Controller_Abstract
{
	//run service
	public function indexAction()
	{
		$telephone = TZ_Request::checkTelephone();
		$password = TZ_Request::checkPassword();

		$params = TZ_Request::getParams('post');
		if (isset($params['name']) && $params['name'] != '') {
			$name = $params['name'];
		} else {
			$name = '747er_'.substr(md5($telephone), 0, 6);
		}
		//register
		$sessionId = TZ_Loader::service('User','User')->register($telephone, $password, $name);
		//response
		TZ_Request::success(array(array('session_id' => $sessionId)));
	}
}
