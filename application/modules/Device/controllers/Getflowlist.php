<?php

/* * 前台购买 获取套餐接口
 * @package compent_cellular_data_service
 * Getflowlist
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 11:45:03
 * @version 1.0
 * @since
 */

class GetflowlistController extends Yaf_Controller_Abstract {

    public function indexAction()
    {
        $params = TZ_Request::getParams('get');
        $sCcid  = $params['ccid'];
        if (strlen($sCcid) == 20)
        {
            $sCcid = substr($sCcid, 0, 19);
        }
        elseif (strlen($sCcid) != 19)
        {
            throw new Exception("您输入的ICCID有误，请检查");
        }
        $result = TZ_Loader::service('Product', 'Device')->getPayFlowList($sCcid);
        TZ_Request::success($result);
    }

}
