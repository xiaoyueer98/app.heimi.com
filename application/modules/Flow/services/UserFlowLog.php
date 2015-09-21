<?php
/**
 * 用户流量日志
 *
 * @author octopus <zhangguipo@747.cn>
 * @final 2014-11-6
 */
class UserFlowLogService
{
    //订单data
    public function getUserFlowLogList($ccid,$startDate,$endDate, $limit, $size)
    {
        return TZ_Loader::model('CronUserFlowLog', 'Flow')->getUserFlowLogList($ccid,$startDate,$endDate, $limit, $size);
    }

}
