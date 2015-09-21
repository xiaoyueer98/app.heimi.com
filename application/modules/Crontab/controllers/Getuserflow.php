<?php

/**
 * @package 自动任务获取联通流量详情
 * Getuserflow
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-17 16:52:20
 * @version 1.0
 * @since
 */
class GetuserflowController extends Yaf_Controller_Abstract
{

    //index
    public function indexAction()
    {

        $sDate = $_GET['date'] ? $_GET['date'] : date("Y-m-d", strtotime("1 days ago"));
        TZ_Loader::service('Flow', 'Crontab')->getUserFlow($sDate);
    }

}
