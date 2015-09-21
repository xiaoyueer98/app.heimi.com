<?php

/**
 * @package 初始化获取流量任务表
 * Initflowjob
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-17 16:52:20
 * @version 1.0
 * @since
 */
class InitflowjobController extends Yaf_Controller_Abstract
{

    //index
    public function indexAction()
    {
        //测试url
        //http://www.ccds.com/crontab/initflowjob/index?date=2014-08-01
        $date = $_GET['date'] ? $_GET['date'] : date("Y-m-d", strtotime("1 days ago"));
        $res  = TZ_Loader::service('Flow', 'Crontab')->InitFlowJob($date);
        if (!$res)
        {
            echo "任务表已经执行!";
        }
        else
        {
            echo "执行成功!";
        }
    }

}
