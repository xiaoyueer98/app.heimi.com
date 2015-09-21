<?php
/**
 * Payresult controller class
 * @author  nick <zhaozhiwei@747.cn>
 * @final 2014-11-28
 */
class ChangecardorderController extends Yaf_Controller_Abstract {
    //购买成功 （3.5G）
    public function indexAction()
    {
        $params = TZ_Request::getParams('post');
		$orderId=$params['orderNumber'];
		$status=$params['status'];
        if($status != 'SUCCESS')
        {
            die("Filed Request.");
        }
		$orderDetail = TZ_Loader::service('Changecardorder', 'Flow')->updateOrder($orderId,2);
        if(!isset($orderDetail['uid']))
        {
            echo "SUCCESS";
            exit;
        }
        $orderInfo = TZ_Loader::service('Order', 'Device')->getOrderDetail($orderId);
        $ret = TZ_Loader::service('Changecardorder', 'Flow')->bindNewCard($orderInfo,$orderDetail);
		echo "SUCCESS";
    }
}
