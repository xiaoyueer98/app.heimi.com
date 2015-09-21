<?php

/* * 获取卡下次可充值月份
 * @package compent_cellular_data_service
 * Getflowmonth
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-28 10:19:28
 * @version 1.0
 * @since
 */

class GetflowmonthController extends Yaf_Controller_Abstract {

    public function indexAction()
    {
        $params    = TZ_Request::getParams('get');
        $sessionId = TZ_Request::checkSessionId('get');
        $uid       = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
        if (!$uid)
        {
            throw new Exception('您还没有登陆，无法继续操作。');
        }

        //ccid
        $sCcid  = $params['ccid'];
        $result = TZ_Loader::service('UserFlow', 'Device')->getFlowNextRechargeMonth($uid, $sCcid);

        TZ_Request::success($result);
    }

}
