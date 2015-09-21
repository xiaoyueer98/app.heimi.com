<?php

/**
 * @package 自动任务获取联通流量详情
 * Getuserflow
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-17 16:52:20
 * @version 1.0
 * @since
 */
class RegetuserflowController extends Yaf_Controller_Abstract
{

    //index
    public function indexAction()
    {

        
        TZ_Loader::service('Flow', 'Crontab')->reGetUserFlow();
    }

}
