<?php

/* * 卡可购买的流量套餐列表
 * @package compent_cellular_data_service
 * Getpackagelist
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 17:49:27
 * @version 1.0
 * @since
 */

class GetpackagelistController extends Yaf_Controller_Abstract {

    public function indexAction()
    {
        $params    = TZ_Request::getParams('get');
        $sessionId = TZ_Request::checkSessionId('get');
        $uid       = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
        if (!$uid)
        {
            throw new Exception('您还没有登陆，无法继续操作。');
        }

        //卡的产品ID
        $sCcid   = $params['ccid'];
        $result = TZ_Loader::service('Product', 'Device')->getPayFlowList($sCcid);

        TZ_Request::success($result);
    }

}
