<?php

/* * 获取卡详情
 * @package compent_cellular_data_service
 * Getcardinfo
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 18:14:53
 * @version 1.0
 * @since
 */

class GetfloworderlistController extends Yaf_Controller_Abstract {

    public function indexAction()
    {

        $params = TZ_Request::getParams('get');
      
        /*
          $sessionId = TZ_Request::checkSessionId('get');

          $uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);


          if (!$uid)
          {
          throw new Exception('您还没有登陆，无法继续操作。');
          }
          $arUserInfo = TZ_Loader::service('User', 'User')->getInfoByUid($uid);
         */


        $ccid   = $params['ccid'];
 
        $result = TZ_Loader::service('UserFlow', 'Flow')->getFlowOderList($ccid);

        TZ_Request::success($result);
    }

}
