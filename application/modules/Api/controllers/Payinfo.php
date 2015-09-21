<?php

/**
 * payinfo controller class
 *
 ntval($proLength)-intval($cityLength)-intval($areaLength)* @author	刑天<wangtongmeng@747.cn>
 * @final 2014-11-28
 */
class PayinfoController extends Yaf_Controller_Abstract {

	/**
	 * 用户订单提交付款
	 * @param  $ccid		卡id
	 * @param  $session_id	用户sessionID
	 */
	public function indexAction() 
	{
        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if (strlen($ccid) == 20) {
            $ccid = substr($ccid, 0, 19);
        } else if (strlen($ccid) != 19) {
            throw new Exception("请检查您的ICCID!");
        }
        $cfArr = TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
        if (empty($cfArr)) {

            $this->_view->display("no_info.html");
            die;
        }

		$sid = $params['session_id'];
		$uid = TZ_Loader::service('SessionManager','User')->getUid($sid);
		if(empty($uid))
		{
			throw new Exception("登录过期，请重新登录");
		}
		$uContent = array('uid:eq' => $uid);
		$userAddress = TZ_Loader::model('Address','Device')->select($uContent,'*','ROW');
		if(!empty($userAddress))
		{
			$proLength = mb_strlen($userAddress['province'],'utf8');
			$cityLength = mb_strlen($userAddress['city'],'utf8');
			$areaLength = mb_strlen(trim($userAddress['area']),'utf8');
			$lastLength = 40-intval($proLength)-intval($cityLength)-intval($areaLength);
            if(mb_strlen(trim($userAddress['detail']),'utf8')>$lastLength)
            {
                $userAddress['detail'] = mb_substr(trim($userAddress['detail']),0,$lastLength,'utf-8').'...';
            }
		}
		$this->_view->assign('userAddress',$userAddress);
		$this->_view->assign('ccid',$ccid);
		$this->_view->assign('sid',$sid);
        $this->_view->display('payinfo.html');
    }
	
	/**
	 * 用户添加收货信息
	 * @param  $session_id	用户sessionID
	 */
	public function addaddressAction() 
	{
		$params = TZ_Request::getParams('get');	
		$sid = $params['session_id'];
		$this->_view->assign('ccid',$params['ccid']);
		$this->_view->assign('sid',$params['session_id']);
        $this->_view->display('address.html');

	}
	
	/**
	 * 处理用户添加收货信息
	 * @param  $session_id	用户sessionID
	 */
	public function addaddressdataAction() 
	{
		$strparams = TZ_Request::getParams('get');	
		$params = TZ_Request::getParams('post');	
		$sid = $strparams['session_id'];

		$uid = TZ_Loader::service('SessionManager','User')->getUid($sid);
		if(empty($uid))
		{
			throw New Exception('登录过期，请重新登录');
		}
		$userInfo = TZ_Loader::service("User", "User")->getInfoByUid($uid); 
		if(empty($userInfo))
		{
			throw New Exception('登录过期，请重新登录!');
		}
		$addArr = array(
			'uid' 			=>	$uid,
			'telephone'		=>	$userInfo['telephone'],
			'name'			=>	$params['username'],	
			'receive_tel'	=>	$params['usertelephone'],
			'province'		=>	$params['homeprov'],
			'city'			=>	$params['homecity'],
			'area'			=>	$params['homedistrict'],
			'detail'		=> 	$params['userdetail'],
			'mailCode'		=>	$params['postcode'],
			'created_at'	=>	date('Y-m-d H:i:s'),
			'updated_at'	=>	date('Y-m-d H:i:s')
		);
		$insertAddress = TZ_Loader::model('Address','Device')->insert($addArr);
		if(empty($insertAddress))
		{
			throw New Exception('系统繁忙，请稍后重试');
		}else
		{
			header("Location:/api/payinfo/index?ccid=".$strparams['ccid']."&session_id=".$strparams['session_id']);
		}
	}

	/**
	 * 用户修改收货信息
	 * @param  $session_id	用户sessionID
	 */
	public function upaddressAction() 
	{
		$params = TZ_Request::getParams('get');	
		$sid = $params['session_id'];
		$uid = TZ_Loader::service('SessionManager','User')->getUid($sid);
		if(empty($uid))
		{
			throw New Exception('登录过期，请重新登录');
		}
		$userInfo = TZ_Loader::service("User", "User")->getInfoByUid($uid); 
		if(empty($userInfo))
		{
			throw New Exception('登录过期，请重新登录!');
		}
		//获取用户收货地址
		$uContent = array('uid:eq' => $uid);
		$userAddress = TZ_Loader::model('Address','Device')->select($uContent,'*','ROW');
		$this->_view->assign('userAddress',$userAddress);
		$this->_view->assign('ccid',$params['ccid']);
		$this->_view->assign('sid',$sid);
        $this->_view->display('address_set.html');

	}

	/**
	 * 处理用户修改收货信息
	 * @param  $session_id	用户sessionID
	 */
	public function upaddressdataAction() 
	{
		$strparams = TZ_Request::getParams('get');	
		$params = TZ_Request::getParams('post');	
		$sid = $strparams['session_id'];
		$uid = TZ_Loader::service('SessionManager','User')->getUid($sid);
		if(empty($uid))
		{
			throw New Exception('登录过期，请重新登录');
		}
		$userInfo = TZ_Loader::service("User", "User")->getInfoByUid($uid); 
		if(empty($userInfo))
		{
			throw New Exception('登录过期，请重新登录!');
		}
		$upArr = array(
			'name'			=>	$params['username'],	
			'receive_tel'	=>	$params['usertelephone'],
			'province'		=>	$params['homeprov'],
			'city'			=>	$params['homecity'],
			'area'			=>	$params['homedistrict'],
			'detail'		=> 	$params['userdetail'],
			'mailCode'		=>	$params['postcode'],
			'updated_at'	=>	date('Y-m-d H:i:s')
		);
		$upCondition = array('uid:eq' => $uid);
		$upAddress = TZ_Loader::model('Address','Device')->update($upArr,$upCondition);
		if(empty($upAddress))
		{
			throw New Exception('系统繁忙，请稍后重试');
		}else
		{
			header("Location:/api/payinfo/index?ccid=".$strparams['ccid']."&session_id=".$strparams['session_id']);
		}
	}

}
