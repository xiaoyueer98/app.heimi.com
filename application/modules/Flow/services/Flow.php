<?php

/** 产品
 * @package compent_cellular_data_service
 * Product
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-10-27 11:45:29
 * @version 1.0
 * @since
 */
class FlowService
{

    /**
     * 获取流量
     * @param  iccid
     * @return string
     */
    public function getFlow($iccid)
    {
        $condition['ccid:eq'] = $iccid;
        return TZ_Loader::model('FlowAccount', 'Flow')->select($condition, '*', 'ALL');
    }

    public function getFid($fid)
    {

        //从flow_type中得到fid(flow_type未有价格的被封装的套餐表)，得到的fid是真正的流量包id
        $condition_ft['id:eq'] = $fid;
        $flowtypeArr           = TZ_Loader::service('Flowtype', 'Device')->getFlowtypeList($condition_ft);
        if (empty($flowtypeArr))
        {
            return false;
        }
        $fidpk                 = $flowtypeArr['fid'];
        //通过得到的fid package 中得到每个月的总流量
        $condition_pk['id:eq'] = $fidpk;
        $packageArr            = TZ_Loader::model('Package', 'Flow')->select($condition_pk, '*', 'ROW');
        if (empty($packageArr))
        {
            return false;
        }
        return $packageArr;
    }

    public function getMonthTotal($iccid)
    {
        $condition                   = array();
        $condition['ccid:eq']        = $iccid;
        $condition['status:eq']      = 2;
        $condition['start_date:elt'] = date("Y-m-d H:i:s");
        $condition['end_date:gt']    = date("Y-m-d H:i:s");
        //从flow_order 表中得到fid（流量套餐ID），当前时间在套餐有效期之间的
        $floworderArr                = TZ_Loader::model('FlowOrder', 'Flow')->select($condition, 'fid', 'ROW');
        
        if (empty($floworderArr))
        {
            $condition              = array();
            $condition['ccid:eq']   = $iccid;
            $condition['status:eq'] = 2;
            $condition['order']     = "end_time desc";
            //从flow_order 表中得到fid（流量套餐ID），当前时间在套餐有效期之间的
            $floworderArr           = TZ_Loader::model('FlowOrder', 'Flow')->select($condition, 'fid', 'ROW');
        }
        $fid = $floworderArr['fid'];
        $packageArr = $this->getFid($fid);
        
        //判断是否是新卡
        $sNewCardID = Yaf_Registry::get('config')->newcardid;
        $cfArr = TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($iccid);
        
        //如果是新卡，有多条充值记录，就将充值记录数量*month_size的值作为总流量返回
        
        //从flow_order 表中得到fid（流量套餐ID），当前时间在套餐有效期之间的
        
        $floworderArrAll = TZ_Loader::model('FlowOrder', 'Flow')->getfFLowOrdersAll($iccid);
        //var_dump($floworderArrAll);
        if($sNewCardID == $cfArr['cpid'] && !empty($floworderArrAll)){
            
            return count($floworderArrAll)*$packageArr['month_size'];
        }else{
            return $packageArr['month_size'];
        }
    }

    //查询流量借口
    public function getSurplusFlow($ctelephone,$dis=false)
    {
        $config     = Yaf_Registry::get('config');
        $url        = $config->user->suplus->url;
        $url        = $url.$ctelephone;
        $flow_json  = file_get_contents($url);
        if($flow_json)
        {
            $flow       = (array)json_decode($flow_json);
            if($flow['msg'] == 'success')
            {
                $rep = array();
                ($flow['result'] > 0) AND $rep['left'] = $flow['result'] OR $rep['left'] = 0;
                ($flow['totalFlow'] > 0) AND $rep['total'] = $flow['totalFlow'] OR $rep['totalFlow'] = 0;
                $rep['time'] = $flow['updateTime'];
                //3.5G卡 总流量不大于3.5G
                if($rep['total'] > 3584 && $flow['packageType'] == 4)
                {
                    $rep['total'] = 3584;
                }
                if($rep['left'] > 3584 && $flow['packageType'] == 4)
                {
                    $rem = floor($rep['left']/3584);
                    $rep['left'] -= $rem * 3584;
                }
                //深圳卡 全国、本地双流量
                if($dis)
                {
                    $rep['unlimit'] = $rep['local'] = 0;
                    if($flow['localFlow'] > 0)
                    {
                        $localFlow = sprintf("%.2f",$flow['localFlow']/1048576);
                        $rep['unlimit'] = $rep['left'];
                        $rep['local']   = $localFlow;
                        $rep['left'] += $localFlow;
                    }
                }
                else
                {
                    if($flow['packageType'] == 8)
                    {
                        $rep['left'] += sprintf("%.2f",$flow['localFlow']/1048576);
                    }
                }
                return $rep;
            }
        }
        else
        {
            return false;
        }
    }

    public function initFlowId()
    {
        $sNewCardPayID     = Yaf_Registry::get('config')->newcardpayid;
        $arNewCardPayIDTmp = explode(",", $sNewCardPayID);
        $arNewCardPayId    = array();
        foreach ($arNewCardPayIDTmp as $sID)
        {
            array_push($arNewCardPayId, intval($sID));
        }
        return $arNewCardPayId;
    }
    //写log
    public function api_log($file,$code)
    {
        $dir = __FILE__;
        $dir = str_replace('application/modules/Flow/services/Flow.php','', $dir);
        $hd = fopen($dir.'test/'.$file,'a+');
        $log = date('Y-m-d H:i:s')."$code \r\n";
        fwrite($hd, $log);
        fclose($hd);
    }
}
