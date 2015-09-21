<?php
/**
 * login controller class
 *
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-25
 */
class LoginController extends Yaf_Controller_Abstract
{
	//run service
	public function indexAction()
	{
		$telephone = TZ_Request::checkTelephone();
		$password = TZ_Request::checkPassword();

		if(!empty($_POST['debug'])){
			$password=hash('sha256',$password);
		}
        if (TZ_Loader::service('Blacklist')->inList($telephone))
             TZ_Request::error('系统检测你有违规操作，帐号已被冻结。', 500);
		$sessionId = TZ_Loader::service('User','User')->login($telephone, $password);
		TZ_Request::success(array(array('session_id' => $sessionId)));
	}
}