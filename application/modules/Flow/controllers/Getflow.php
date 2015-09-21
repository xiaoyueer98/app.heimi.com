<?php
/* 
 * 获取套餐接口
 * @package compent_cellular_data_service
 * @author nick<zhaozhiwei@747.cn>
 */

class GetflowController extends Yaf_Controller_Abstract
{
    //用户实时查询剩余流量接口    
    public function indexAction()
    {
        if (!isset($_GET['iccid']))
        {
            throw new Exception("参数错误");
        }
        else
        {
            $iccid = $_GET['iccid'];
        }
        //通过查来的iccid，得到用户的电话号码
        if (strlen($iccid) == 20)
        {
            $iccid = substr($iccid, 0, 19);
        }
        elseif (strlen($iccid) != 19)
        {
            throw new Exception("请检查您的ICCID!");
        }
        $data = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($iccid);

        if (empty($data) || count($data) == 0 || !isset($data['ctelephone']))
        {
            //不存在于我们的数据库中
            throw new Exception("未查到相关信息，请确认是否为747流量卡！");
        }
        //得到去联通官网查询时所需的电话号码
        $ctelephone = $data['ctelephone'];
        $flow = TZ_Loader::service('Flow', 'Flow')->getSurplusFlow($ctelephone);
        if($flow)
        {
            TZ_Request::success(array($flow));
        }
        else
        {
            TZ_Request::error('网络繁忙');
        }
    }
}
