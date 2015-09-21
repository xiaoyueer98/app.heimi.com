<?php
/**
 * order controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class OrderController extends Yaf_Controller_Abstract {

	public function indexAction()
	{

		$params = TZ_Request::getParams('post');
		$sessionId = TZ_Request::checkSessionId('post');
		$uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
		if (!$uid){
			throw new Exception('您还没有登陆，无法继续操作。');
		}
		//  "params":"session_id=18600622921&did=123&cid=123&fid=123"
		$did=$cid=$fid=0;
		//可以单买盒子，也可以单买卡和流量，还可以都买。
		if(isset($params['did'])&&is_numeric($params['did']) ){
			$did=$params['did'];
			if(isset($params['cid'])&&is_numeric($params['cid'])&&isset($params['fid'])&&is_numeric($params['fid'])){
				$cid=$params['cid'];
				$fid=$params['fid'];
			}
		}elseif (isset($params['cid'])&&is_numeric($params['cid'])&&isset($params['fid'])&&is_numeric($params['fid'])){
			$cid=$params['cid'];
			$fid=$params['fid'];
		}else{
			throw new Exception('对不起，参数错误。');
		}
		$arUserInfo = TZ_Loader::service('User', 'User')->getInfoByUid($uid);
		//1：需要绑定 其余解除绑定
		$type       = ($params['type'] > 0 ? $params['type'] : 1);
		$ccid       = $params['ccid'];
		$result     = TZ_Loader::service('UserFlow', 'Flow')->setUserCard($uid, $ccid, $arUserInfo['telephone'], $type);

		TZ_Request::success($result);
	}
        public function testAction(){
//                $orderId = '278511470583483047';
//                @$re = TZ_Loader::service('Order', 'Device')->setOrder($orderId);
//                var_dump($re);
            
        }

}
