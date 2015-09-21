<?php

/* * 绑定或者解除绑定
 * @package compent_cellular_data_service
 * Bindcard
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 18:14:53
 * @version 1.0
 * @since
 */

class DelcardController extends Yaf_Controller_Abstract
{

    public function indexAction()
    {

        $params = TZ_Request::getParams('get');

        $sessionId = TZ_Request::checkSessionId('get');

        $uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
        if (!$uid)
        {
            throw new Exception('您还没有登陆，无法继续操作。');
        }

        //1：需要绑定 其余解除绑定

        $ccid = $params['iccid'];
        if (strlen($ccid) == 20)
        {
            $ccid = substr($ccid, 0, 19);
        }
        elseif (strlen($ccid) != 19)
        {
            throw new Exception("您输入的ICCID有误，请检查");
        }
        $result = TZ_Loader::service('UserFlow', 'Flow')->delUserCard($uid, $ccid);

        TZ_Request::success(array(array( "message" => "操作成功")));
    }

}
