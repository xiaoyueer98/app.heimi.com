<?php

/** 产品
 * @package compent_cellular_data_service
 * Product
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 11:45:29
 * @version 1.0
 * @since
 */
class ProductService
{

    //产品类型
    static private $_product_type = array(1, 2);

    /**
     * 获取可用产品列表
     * @param  int $type 产品类型 1,盒子，2,卡 0 全部
     * @return array
     */
    public function getProductList($type = 0)
    {

        $condition['status:eq']      = 1;
        $condition['start_time:elt'] = date("Y-m-d H:i:s");
        $condition['ent_time:gt']    = date("Y-m-d H:i:s");
        if ($type > 0 && in_array($type, self::$_product_type))
        {
            $condition['type:eq'] = $type;
        }
        $condition['order'] = "updated_at DESC";
        $arProductList      = TZ_Loader::model('Product', 'Device')->select($condition, '*', 'ALL');
        return $arProductList;
    }

    /**
     * 获取可用产品列表
     * @param  int $sCardId 卡ID
     * @return array
     */
    public function getFlowList($sCardId = 0)
    {
        if ($sCardId > 0)
        {
            $condition['status:eq'] = 1;
            $condition['pid:eq']    = $sCardId;
            //获取卡流量关系
            $arFlowCardList         = TZ_Loader::model('CardFlowType', 'Device')->select($condition, '*', 'ALL');

            //获取流量熟悉
            if (!empty($arFlowCardList))
            {
                $arCardFlow = array();
                foreach ($arFlowCardList as $arFlowCard)
                {
                    $condition                   = array();
                    $condition['id:eq']          = $arFlowCard['fid'];
                    $condition['status:eq']      = 1;
                    $condition['start_time:elt'] = date("Y-m-d H:i:s");
                    $condition['ent_time:gt']    = date("Y-m-d H:i:s");
                    $condition['type:eq']        = 1;
                    //流量套餐info
                    $arFlowInfo                  = TZ_Loader::model('FlowType', 'Device')->select($condition, '*', 'ROW');
                    if (!empty($arFlowInfo))
                    {
                        array_push($arCardFlow, $arFlowInfo);
                    }
                }
                return $arCardFlow;
            }
            else
            {
                return array();
            }
        }
        else
        {
            throw new Exception('产品ID不存在.');
        }
    }

    /**
     * 获取可用产品列表
     * @param  int $uid 用户ID
     * @return array
     */
    public function getCardList($uid)
    {


        //获取卡流量关系
        $arUserFlowList = TZ_Loader::service('UserFlow', 'Flow')->getUserCard($uid);


        if (!empty($arUserFlowList))
        {

            $arUserCardList = array();
            foreach ($arUserFlowList as $arProduct)
            {
                $sCcid              = $arProduct['ccid'];
                //获取卡硬件对应的详情
                $arCardInfo         = TZ_Loader::model('Product', 'Device')->select(array("id:eq" => $arProduct['cpid']), 'name,demo', 'ROW');
                $arCardInfo['demo'] = preg_replace("/<br>/is", "", $arCardInfo['demo']);
                $arCardInfo['ccid'] = $sCcid;

                $arCardInfo['status'] = TZ_Loader::service('UserFlow', 'Flow')->getCardStatus($arProduct['cpid'], $sCcid);
                array_push($arUserCardList, $arCardInfo);
            }
            return $arUserCardList;
        }
        else
        {
            return array();
        }
    }

    /**
     * 获取可充值的卡套餐列表
     * @param  int $sCcid ccid
     * @return array
     */
    public function getPayFlowList($sCcid)
    {

        $arrOrderInfo = $arProduct    = TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($sCcid);

        $sPid                   = $arrOrderInfo['cpid'];
        $condition['status:eq'] = 1;
        $condition['pid:eq']    = $sPid;

        //获取卡流量关系
        $arFlowCardList = TZ_Loader::model('CardFlowType', 'Device')->select($condition, '*', 'ALL');


        //获取流量熟悉
        if (!empty($arFlowCardList))
        {
            $arCardFlow = array();
            foreach ($arFlowCardList as $arFlowCard)
            {
                $condition                   = array();
                $condition['id:eq']          = $arFlowCard['fid'];
                $condition['status:eq']      = 1;
                $condition['start_time:elt'] = date("Y-m-d H:i:s");
                $condition['ent_time:gt']    = date("Y-m-d H:i:s");
                $condition['type:eq']        = 2;
                //流量套餐info
                $arFlowInfo                  = TZ_Loader::model('FlowType', 'Device')->select($condition, '*', 'ROW');


                if (!empty($arFlowInfo))
                {
                    
                    $arNewCardPayId = TZ_Loader::service('Flow', 'Flow')->initFlowId();
                    if (in_array($arFlowInfo['id'],$arNewCardPayId))
                    {
                        $arFlowInfo['next_month'] = "自激活起180天有效";
                        $arFlowInfo['pay_start_date'] = date("Y-m")."-01";
                        $arFlowInfo['pay_end_date']   = $nextMonth = date("Y-m-t", strtotime((date("Y-m") . "-01") . "+6 month "));
                    }
                    else
                    {
                        $arNext_month             = TZ_Loader::service('UserFlow', 'Flow')->getFlowNextRechargeMonth($sCcid, $arFlowCard['fid']);
                        $arFlowInfo['next_month'] = $arNext_month['nextstr'];
                        $arFlowInfo['pay_start_date'] = $arNext_month['pay_start_date'];
                        $arFlowInfo['pay_end_date']   = $arNext_month['pay_end_date'];
                    }
                    array_push($arCardFlow, $arFlowInfo);
                }
            }
            return $arCardFlow;
        }
        else
        {
            return array();
        }
    }

    //获取套餐详情
    public function getFlowInfo($id)
    {
        return TZ_Loader::model('FlowType', 'Device')->select(array("id:eq" => $id), '*', 'ROW');
    }

}
