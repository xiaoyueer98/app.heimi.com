<?php
/**
 * Receive controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class ReceiveController extends Yaf_Controller_Abstract {

	public function indexAction()
	{
		$params = TZ_Request::getParams('post');
		$orderId=$params['orderNumber'];
	//	$price=$params['price'];
		$status=$params['status'];
		//$key=$params['key'];
//		$result=0;
//		//判断该请求是否来源自支付中心
//		$sec = hash('sha256',$orderId.$price.$status);
//		if($key == $sec){
//			TZ_Loader::service('Order', 'Device')->setOrder($orderId,$status);
//		}
		TZ_Loader::service('Order', 'Device')->setOrder($orderId,$status);
		echo "SUCCESS";
		//TZ_Request::success($result);

	}

}
