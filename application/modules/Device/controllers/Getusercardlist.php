<?php

/* * 获取用户购买的卡列表
 * @package compent_cellular_data_service
 * Getusercardlist
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 11:45:03
 * @version 1.0
 * @since
 */

class GetusercardlistController extends Yaf_Controller_Abstract {

    public function indexAction()
    {
        $params = TZ_Request::getParams('get');
        $sessionId = TZ_Request::checkSessionId('get');
        $uid       = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
        if (!$uid)
        {
            throw new Exception('您还没有登陆，无法继续操作。');
        }


        $result = TZ_Loader::service('Product', 'Device')->getCardList($uid);

        TZ_Request::success($result);
    }

}
