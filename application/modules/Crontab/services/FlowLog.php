<?php

/**
 * @package compent_cellular_data_service
 * FlowLog
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-17 16:52:20
 * @version 1.0
 * @since
 */
class FlowLogService
{

    //初始化用户充值方案表
    public function InitFlowLog($date)
    {
        $isExec = $this->getFlowLog($date);
        if (!empty($isExec) && count($isExec) > 0)
        {
            return "当前已经执行成功！";
        }
        else
        {
            $arUserPlanList = $this->getUserPayPlan($date);
            if (!empty($arUserPlanList) && count($arUserPlanList) > 0)
            {
                $res = TZ_Loader::model("FlowLog", "Flow")->insert($arUserPlanList);
                if ($res)
                {
                    return "执行成功！";
                }
                else
                {
                    return "执行失败！";
                }
            }
            else
            {

                return "当前没有可充值数据！";
            }
        }
    }

    //获取用户充值日志
    public function getFlowLog($date)
    {
        return TZ_Loader::model("FlowLog", "Flow")->select(array("pay_stime:eq" => $date), "*", "ALL");
    }

    //获取用户充值计划
    public function getUserPayPlan($date)
    {
        return TZ_Loader::model("PayPlan", "Flow")->getUserPlanList($date);
    }

}
