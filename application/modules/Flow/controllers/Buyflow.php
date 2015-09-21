<?php

/**
 * buyflow controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class BuyflowController extends Yaf_Controller_Abstract {

    public function indexAction() {

        $params = TZ_Request::getParams('post');
        if (!empty($params['session_id'])) {
            $sessionId = TZ_Request::checkSessionId('post');
            $uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
            if (!$uid) {
                //throw new Exception('您还没有登陆，无法继续操作。');
                $uid = 0;
            }
        } else {
            $uid = 0;
        }
        //  "params":"session_id=18600622921&fid=123&ccid=asfdafdafdsafdas&starttime=20141101"
        if (!isset($params['fid']) || !isset($params['ccid']) || !isset($params['starttime']) || !isset($params['endtime'])) {
            throw new Exception('对不起,参数异常。');
        }
        $fid = $params['fid'];
        $ccid = $params['ccid'];
        $starttime = $params['starttime'];
        $endtime = $params['endtime'];
        $result = TZ_Loader::service('Order', 'Device')->buyFlow($uid, $fid, $ccid, $starttime, $endtime);
        TZ_Request::success($result);
    }

    public function changeAction() {
        $params = TZ_Request::getParams('post');
        $sessionId = TZ_Request::checkSessionId('post');
        $uid = TZ_Loader::service('SessionManager', 'User')->getUid($sessionId);
        if (!$uid) {
            throw new Exception('您还没有登陆，无法继续操作。');
        }
        //  "params":"session_id=18600622921&fid=123&ccid=asfdafdafdsafdas&starttime=20141101"
        if (!isset($params['fid']) || !isset($params['ccid']) || !isset($params['starttime']) || !isset($params['endtime'])) {
            throw new Exception('对不起,参数异常。');
        }
        $fid = $params['fid'];
        $ccid = $params['ccid'];
        $starttime = $params['starttime'];
        $endtime = $params['endtime'];
        $result = TZ_Loader::service('Order', 'Device')->buyCard($uid, $fid, $ccid, $starttime, $endtime, $sessionId);
        TZ_Request::success($result);
    }

}
