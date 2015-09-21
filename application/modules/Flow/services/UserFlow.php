<?php

/**
 * @package compent_cellular_data_service
 * UserFlow
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 15:12:03
 * @version 1.0
 * @since
 */
class UserFlowService
{

    /**
     * 获取流量
     * @param  iccid
     * @return string
     */
    public function getUserCard($uid)
    {

        return TZ_Loader::model('UserCard', 'Flow')->select(array("uid:eq" => $uid, "status:eq" => 3), "status,ccid,cpid", "ALL");
    }

    /**
     * 用户绑定卡
     * @param  $uid 用户id
     * @param  $ccid 卡ID
     * @param  $telephone 用户电话
     * @param  $type 1绑定 其余解除绑定
     * @return string
     */
    public function setUserCard($uid, $ccid, $telephone, $type)
    {
        $type       = ($type == 1 ? 3 : 4);
        $arCardInfo = $this->getFlowProductId($ccid);

        if (!empty($arCardInfo))
        {
            $arUserCardInfo = TZ_Loader::model('UserCard', 'Flow')->select(array("uid:eq" => $uid, "ccid:eq" => $ccid), "*", "ROW");
            if (!empty($arUserCardInfo))
            {
                //如果此卡绑定成功还想绑定提示
                if ($arUserCardInfo['status'] == 3 && $type == 3)
                {

                    throw new Exception("此卡已经绑定成功！");
                }
                try
                {

                    $arUserCardInfo = TZ_Loader::model('UserCard', 'Flow')->update(array("status" => $type, "updated_at" => date("Y-m-d H:i:s")), array("uid:eq" => $uid, "ccid:eq" => $ccid));
                    return 1;
                }
                catch (Exception $exc)
                {
                    throw new Exception("操作失败！");
                }
            }
            else
            {
                if ($type == 4)
                {
                    throw new Exception("此卡还未绑定过，无法解除绑定！");
                }
                //插入绑定关系
                $arUserCard               = array();
                $arUserCard['cpid']       = $arCardInfo['cpid'];
                $arUserCard['ctelephone'] = $arCardInfo['ctelephone'];
                $arUserCard['uid']        = $uid;
                $arUserCard['telephone']  = $telephone;
                $arUserCard['ccid']       = $ccid;
                $arUserCard['status']     = $type;
                $arUserCard['updated_at'] = $arUserCard['created_at'] = date("Y-m-d H:i:s");
                TZ_Loader::model('UserCard', 'Flow')->insert($arUserCard);
                //查询订单信息
                $orderInfo                = TZ_Loader::model('FlowOrder', 'Flow')->select(array("order_id:eq" => 0, "ccid:eq" => $ccid), '*', 'ROW');
                if (!isset($orderInfo['ccid']))
                {
                    return 1;
                }
                $sendStatus = $orderInfo['fee_status'];
                //给用户送话费
                if ($orderInfo['fee_id'] > 0 && empty($orderInfo['fee_status']))
                {
                    $sendStatus = $this->sendFee($uid, $orderInfo['fee_id']);
                    if (!empty($sendStatus))
                    {
                        $sendFee    = $sendStatus;
                        $sendStatus = 1;
                    }
                }

                $sOrderId = TZ_Loader::service('IdManager', 'User')->createUID();
                $set      = array("order_id"   => $sOrderId, "fee_status" => $sendStatus, "uid"        => $uid,
                    "telephone"  => $telephone, "updated_at" => date("Y-m-d H:i:s"));
                $con      = array("order_id:eq" => 0, "ccid:eq" => $ccid);
                //修改订单用户信息
                TZ_Loader::model('FlowOrder', 'Flow')->update($set, $con);
                if ($sendStatus === 1)
                {
                    return $sendFee;
                }
                return 1;
            }
        }
        else
        {
            throw new Exception("未查到相关信息，请确认是否为747流量卡！");
        }
    }

    /**
     * 解除绑定
     * @param  $uid 用户id
     * @param  $ccid 卡ID
     * @return string
     */
    public function delUserCard($uid, $ccid)
    {

        $arCardInfo = TZ_Loader::model('UserCard', 'Flow')->select(array("ccid:eq" => $ccid, "uid:eq" => $uid), "*", "ROW");

        if (!empty($arCardInfo))
        {
            TZ_Loader::model('UserCard', 'Flow')->update(array("status" => 4, "updated_at" => date("Y-m-d H:i:s")), array("uid:eq" => $uid, "ccid:eq" => $ccid));
            return true;
        }
        else
        {
            throw new Exception("IICID有误，请检查！");
        }
    }

    /**
     * 调用送话费请求
     * @param int $uid
     * @param int $pid
     */
    public function sendFee($uid, $pid)
    {
        $config  = Yaf_Registry::get('config');
        $url     = $config->voip->sendfee->url;
        $opts    = array('http' => array('method' => "GET"));
        $context = stream_context_create($opts);
        //$uid = "122778489836276048";
        $param   = "?uid=" . $uid . "&appName=box&pid=" . $pid;
        $result  = file_get_contents($url . $param, false, $context);
        $data    = json_decode($result, true);

        if ($data['code'] == 200)
        {
            return $data['data']['message'];
        }
        return false;
    }
    /**
     * 获取用户下次充值可选的月份
     * @param  $uid 用户id
     * @param  $pid 卡ID
     * @return string
     */
    public function getFlowNextRechargeMonth($ccid, $pid)
    {

        $arFlowInfo = TZ_Loader::service('Flow', 'Flow')->getFid($pid);

        $sTimes          = $arFlowInfo['size'] / $arFlowInfo['month_size'];
        $arNextMonth     = array();
        $arUserOrderInfo = TZ_Loader::model('FlowOrder', 'Flow')->select(array("ccid:eq" => $ccid, "status:eq" => 2, "order" => "end_date DESC"), "*", "ROW");
      
        if (!empty($arUserOrderInfo))
        {
            $sUserEndDate = $arUserOrderInfo['end_date'];

            for ($i = 1; $i <= $sTimes; $i++)
            {

                $nextMonth = date("Y-m-d", strtotime(date("Y-m", strtotime($sUserEndDate)) . "-01" . "+$i month "));

                array_push($arNextMonth, $nextMonth);
            }
        }
        else
        {
            //可购买当月
            for ($i = 0; $i < $sTimes; $i++)
            {
                $nextMonth = date("Y-m", strtotime((date("Y-m") . "-01") . "+$i month "));
                array_push($arNextMonth, $nextMonth);
            }
        }

        return array("pay_start_date" => date("Y-m", strtotime($arNextMonth[0])) . "-01", "pay_end_date" => date("Y-m-t", strtotime($arNextMonth[count($arNextMonth) - 1])), "nextstr" => date("Y-m", strtotime($arNextMonth[0])) . "到" . date("Y-m", strtotime($arNextMonth[count($arNextMonth) - 1])) . " 每月" . self::initFlow($arFlowInfo['month_size']));
    }

    /**
     * 获取ccid卡状态
     * @param  $pid 硬件卡ID
     * @param  $sCcid 卡ID
     * @return string
     */
    public function getCardStatus($pid, $sCcid)
    {
        $arPayOrder = TZ_Loader::model('FlowOrder', 'Flow')->select(array("ccid:eq" => $sCcid, "order" => "end_date DESC", "status:eq" => 2), "*", "ROW");
        if (!empty($arPayOrder))
        {

            if ($arPayOrder['end_date'] > date("Y-m-d H:i:s"))
            {
                //正在使用
                $status = '可用';
            }
            else
            {
                $arFlowlist = TZ_Loader::model('CardFlowType', 'Device')->select(array("pid:eq" => $pid, "status:eq" => 1), "*", "ALL");
                if (!empty($arFlowlist))
                {
                    foreach ($arFlowlist as $arTmpFlowInfo)
                    {
                        $con      = array("id:eq" => $arTmpFlowInfo['fid'], "status:eq" => 1, "start_time:elt" => date("Y-m-d H:i:s"), "ent_time:gt" => date("Y-m-d H:i:s"));
                        $flowInfo = TZ_Loader::model('FlowType', 'Device')->select($con, "*", "ROW");
                        if (!empty($flowInfo))
                        {
                            //暂停
                            $status = '欠费';
                            break;
                        }
                    }
                }
                else
                {
                    //报废
                    $status = '无效';
                }
            }
        }
        else
        {
            $arFlowlist = TZ_Loader::model('CardFlowType', 'Device')->select(array("pid:eq" => $pid, "status:eq" => 1), "*", "ALL");
            if (!empty($arFlowlist))
            {
                foreach ($arFlowlist as $arTmpFlowInfo)
                {
                    $con      = array("id:eq" => $arTmpFlowInfo['fid'], "status:eq" => 1, "start_time:elt" => date("Y-m-d H:i:s"), "ent_time:gt" => date("Y-m-d H:i:s"));
                    $flowInfo = TZ_Loader::model('FlowType', 'Device')->select($con, "*", "ROW");
                    if (!empty($flowInfo))
                    {
                        //暂停
                        $status = '欠费';
                        break;
                    }
                }
            }
            else
            {
                //报废
                $status = '无效';
            }
        }
        return $status;
    }

    //获取卡对应的月份
    public function getCardMonthInfo($ccid)
    {
        //获取当前正在用的订单
        $condition                = array();
        //$condition['start_date:elt'] = date("Y-m-d H:i:s");
        $condition['end_date:gt'] = date("Y-m-d H:i:s");
        $condition['status:eq']   = 2;
        $condition['ccid:eq']     = $ccid;
        $arCurrentFlow            = TZ_Loader::model('FlowOrder', 'Flow')->select($condition, "*", "ROW");

        $arrCurrentLast = array();
        //如果当前有订单，先找出当前订单
        if (!empty($arCurrentFlow))
        {
            $arFlowInfo = TZ_Loader::service('Flow', 'Flow')->getFid($arCurrentFlow['fid']);

            if (!empty($arFlowInfo))
            {

                $arrCurrentLast = $this->initMonth($arFlowInfo, $arCurrentFlow);
            }
            $condition = array();

            $condition['status:eq'] = 2;
            $condition['ccid:eq']   = $ccid;
            $condition['id:gt']     = $arCurrentFlow['id'];
            $condition['order']     = "start_date";
            $arFlowOrderList        = TZ_Loader::model('FlowOrder', 'Flow')->select($condition, "*", "ALL");

            if (!empty($arFlowOrderList))
            {
                $arLastMonth = array();
                foreach ($arFlowOrderList as $value)
                {

                    $arFlowInfo = TZ_Loader::service('Flow', 'Flow')->getFid($value['fid']);

                    if (!empty($arFlowInfo))
                    {

                        $arLastMonth[] = $this->initMonth($arFlowInfo, $value);
                    }
                }
                foreach ($arLastMonth as $arMonth)
                {
                    foreach ($arMonth as $aMonth)
                    {
                        array_push($arrCurrentLast, $aMonth);
                    }
                }

                return $arrCurrentLast;
            }
            else
            {
                return $arrCurrentLast;
            }
        }
        else
        {
            $condition = array();

            $condition['status:eq']   = 2;
            $condition['ccid:eq']     = $ccid;
            $condition['end_date:gt'] = date("Y-m-d H:i:s");
            $condition['order']       = "start_date";
            $arFlowOrderList          = TZ_Loader::model('FlowOrder', 'Flow')->select($condition, "*", "ALL");

            if (!empty($arFlowOrderList))
            {
                $arLastMonth = array();
                $arrMonth    = array();
                foreach ($arFlowOrderList as $value)
                {

                    $arFlowInfo = TZ_Loader::service('Flow', 'Flow')->getFid($value['fid']);

                    if (!empty($arFlowInfo))
                    {

                        $arLastMonth[] = $this->initMonth($arFlowInfo, $value);
                    }
                }

                foreach ($arLastMonth as $arMonth)
                {
                    foreach ($arMonth as $aMonth)
                    {
                        array_push($arrMonth, $aMonth);
                    }
                }

                return $arrMonth;
            }
            else
            {
                return array();
            }
        }
    }

    //获取卡信息
    public function getProductCardInfo($ccid)
    {
        $arMonthFlow = $this->getCardMonthInfo($ccid);

        $arCardInfo = $this->getFlowProductId($ccid);
        //得到卡的硬件信息
        $arProductInfo  = TZ_Loader::model('Product', 'Device')->select(array("id:eq"=>$arCardInfo['cpid']),"*","ROW");
        //判断用户月份的初始是本月，从联通获取本月还剩余多少流量
        if (!empty($arMonthFlow))
        {


            foreach ($arMonthFlow as $key => &$arMonthInfo)
            {
                if ($key == 0)
                {
                    $sKey = key($arMonthInfo);

                    if (strtotime($sKey . "-" . "00") == strtotime(date("Y-m" . "-00")))
                    {

                        $arMonthInfo[$sKey] = 0;
                    }
                }
            }
        }
        else
        {
            $arUserFlowOrderList = $this->getUserFlowOrder($ccid);
            $sEndTime            = date("Y-m", strtotime($arUserFlowOrderList[0]['end_date']));

            $arMonthFlow = array(array($sEndTime => "-1"));
        }

        return array("ccid" => $ccid, "description" => $arProductInfo['demo'], "name" => $arProductInfo['name'], "flow_list" => $arMonthFlow);
    }

    
    //获取当前用户最近的套餐详情
    public function getCurrentFlowInfo($ccid)
    {
        $con                  = array();
        $con['status:eq']     = 2;
        $con['end_date:gt'] = date("Y-m-d H:i:s");
        $con['ccid:eq']       = $ccid;
        $con['order']         = "id ASC";
        $arCurrentFlow        = TZ_Loader::model('FlowOrder', 'Flow')->select($con, "fid", "ROW");
        if ($arCurrentFlow['fid'] > 0)
        {
            return TZ_Loader::model('FlowType', 'Device')->select(array("id:eq" => $arCurrentFlow['fid']), "id,demo,name", "ROW");
        }
        else
        {
            $con = array();
            $con['status:eq'] = 2;
            $con['ccid:eq']   = $ccid;
            $con['order']     = "end_date DESC";
            
            $arLastFlow       = TZ_Loader::model('FlowOrder', 'Flow')->select($con, "fid", "ROW");
            return TZ_Loader::model('FlowType', 'Device')->select(array("id:eq" => $arLastFlow['fid']), "id,demo,name", "ROW");
        }
    }
    
    //获取跨越不清零的卡得信息
    public function getProductCardInfoYear($ccid)
    {

        //得到卡的相关信息。主要是ctelephone
        $arCardInfo = $this->getFlowProductId($ccid);

        //得到卡的硬件信息
        $arProductInfo  = TZ_Loader::model('Product', 'Device')->select(array("id:eq"=>$arCardInfo['cpid']),"*","ROW");
        if (empty($arProductInfo))
        {
            throw new Exception("网络繁忙");
        }
        return array("ccid" => $ccid, "description" => $arProductInfo['demo'], "name" => $arProductInfo['name']);
    }

    public function initMonth($arFlowInfo, $arUserFlowInfo)
    {


        $times       = $arFlowInfo['size'] / $arFlowInfo['month_size'];
        $arFlowMonth = array();

        for ($i = 0; $i < $times; $i++)
        {

            if (date("m", strtotime($arUserFlowInfo['start_date'])) + $i > 12)
            {

                $sYyyy = date("Y", strtotime($arUserFlowInfo['start_date'])) + 1;
                $sMm   = ((date("m", strtotime($arUserFlowInfo['start_date'])) + $i) - 12);
            }
            else
            {

                $sYyyy = date("Y", strtotime($arUserFlowInfo['start_date']));
                $sMm   = date("m", strtotime($arUserFlowInfo['start_date'])) + $i;
            }
            if($sMm<10){
                $sMm = "0".$sMm;
            }
            if (strtotime($sYyyy . "-" . $sMm . "-00") >= strtotime(date("Y-m-" . "00")))
            {
                $arFlowMonth[] = array($sYyyy . "-" . $sMm => $arFlowInfo['month_size']);
            }
        }
        return $arFlowMonth;
    }

    /**
     * 获取用户流量订单
     * @param  $sCcid 卡ID
     * @return string
     */
    public function getFlowOderList($sCcid)
    {
            $arOrderList = TZ_Loader::model('FlowOrder', 'Flow')->select(array("ccid:eq" => $sCcid, "status:eq" => 2), "user_start_date,user_end_date,fid,status,start_date,created_at", "ALL");
        if (!empty($arOrderList))
        {
            foreach ($arOrderList as &$arOrderInfo)
            {
                $arFlowInfo = TZ_Loader::service('Product', 'Device')->getFlowInfo($arOrderInfo['fid']);

                $arFlow = TZ_Loader::model('Package', 'Flow')->select(array("id:eq" => $arFlowInfo['fid']), "month_size", "ROW");

                $arOrderInfo['title'] = $arFlowInfo['name'];

                if ($arOrderInfo['status'] == 2)
                {
                    $arOrderInfo['status'] = "支付成功";
                }
                else
                {
                    $arOrderInfo['status'] = "支付失败";
                }

                $arNewCardPayId = TZ_Loader::service('Flow', 'Flow')->initFlowId();

                if (in_array($arOrderInfo['fid'], $arNewCardPayId))
                {
                    $arOrderInfo['description'] = "<p class=\"sh_hi_p2\">充值时间：".date('Y-m-d', strtotime($arOrderInfo['created_at']))."</p>";
                }
                else
                {
                    $arOrderInfo['description'] = "<p class=\"sh_hi_p2\">".date("Y-m", strtotime($arOrderInfo['user_start_date'])) . "到" . date("Y-m", strtotime($arOrderInfo['user_end_date'])) . "  每月" . self::initFlow($arFlow['month_size'])."</p>";
                }
            }
        }
        return $arOrderList;
    }

    static private function initFlow($M)
    {
        $arMG = array(1024 => 1, 2048 => 2, 3072 => 3, 4096 => 4, 5120 => 5, 6144 => 6, 7168 => 7, 8192 => 8, 9216 => 9, 10240 => 10, 11264 => 11, 12288 => 12, 13312 => 13, 14336 => 14, 15360 => 15, 16384 => 16, 17408 => 17, 18432 => 18, 19456 => 19, 20480 => 20);
        if (!empty($arMG[$M]))
        {
            return $arMG[$M] . "G";
        }
        else
        {
            return $M . "M";
        }
    }

    //根据ccid返回此卡的产品ID
    public function getFlowProductId($ccid)
    {
        //return TZ_Loader::model('CardFlow', 'Flow')->select(array("ccid:eq" => $ccid), "*", "ROW");
        $cfArr = (array)TZ_Loader::service('CardCenter', 'Flow')->getCardInfoByCCID($ccid);
        return array(
            'ccid' => $cfArr['ccID'],
            'cpid'=>$cfArr['packageID'],
            'ctelephone'=>$cfArr['telephone']
        );
    }

    //获取用户流量订单
    public function getUserFlowOrder($ccid)
    {
        $con = array("ccid:eq" => $ccid, "status:eq" => 2, "order" => "end_date DESC");
        return TZ_Loader::model('FlowOrder', 'Flow')->select($con, "*", "ALL");
    }

    //应用接口
    public function getFlowInfo($ccid)
    {
        $arCardInfo = $this->getFlowProductId($ccid);
        if (empty($arCardInfo))
        {
            throw new Exception("未查到相关信息，请确认是否为747流量卡！");
        }
        //得到卡的硬件信息
        $arProductInfo  = TZ_Loader::model('Product', 'Device')->select(array("id:eq"=>$arCardInfo['cpid']),"*","ROW");
        $sCardStatus = $this->getCardStatus($arCardInfo['cpid'], $ccid);
        return array("ccid" => $ccid, "demo" => $arProductInfo['demo'], "name" => $arProductInfo['name'], "status" => $sCardStatus);
    }

}
