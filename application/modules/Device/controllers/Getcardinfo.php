<?php

/* * 获取卡详情
 * @package compent_cellular_data_service
 * Getcardinfo
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 18:14:53
 * @version 1.0
 * @since
 */

class GetcardinfoController extends Yaf_Controller_Abstract
{

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


        $ccid = $params['ccid'];
        if (strlen($ccid) == 20)
        {
            $ccid = substr($ccid, 0, 19);
        }
        elseif (strlen($ccid) != 19)
        {
            throw new Exception("您输入的ICCID有误，请检查");
        }
        if ($ccid > 0)
        {
            //应用调取接口
            if (isset($params['type']) && intval($params['type']) > 0)
            {
                $result = TZ_Loader::service('UserFlow', 'Flow')->getFlowInfo($ccid);
                $result['demo'] = preg_replace("/<br>/is", "", $result['demo']);
                TZ_Request::success(array($result));
            }
            else
            {
                $result = TZ_Loader::service('UserFlow', 'Flow')->getProductCardInfo($ccid);
                TZ_Request::success(array($result));
            }
        }
        else
        {
            throw new Exception("参数错误");
        }
    }

}
