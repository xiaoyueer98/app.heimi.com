<?php

/**
 * @package compent_cellular_data_service
 * Inituserflowlog
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-17 16:52:20
 * @version 1.0
 * @since
 */
class InituserflowlogController extends Yaf_Controller_Abstract
{

    //index
    public function indexAction()
    {
        //测试url
        //http://www.ccds.com/crontab/inituserflowlog/index?date=2014-08-01
        $date = $_GET['date'] ? $_GET['date'] : date("Y-m-d", strtotime("1 days ago"));
        $res  = TZ_Loader::service('FlowLog', 'Crontab')->InitFlowLog($date);
        echo $res;
    }

}
