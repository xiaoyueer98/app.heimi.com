<?php
/**
 * 用户流量日志
 *
 * @author nick <zhaozhiwei@747.cn>
 * @final 2014-11-27
 */
class ChangecardorderService
{
    //订单支付成功
    public function updateOrder($orderId,$status)
    {
        //查询订单信息
		$orderInfo=TZ_Loader::model('Orders','Device')->select(array('oid:eq'=>$orderId), '*', 'ROW');
		if(!isset($orderInfo['oid'])){
			throw new Exception('对不起当前订单有问题，请联系客服。');
		}
		if($orderInfo['status'] == 2){
			return true;
		}
		//更新订单状态
		$condition = $set = array();
		$condition['oid:eq'] = $orderId;
		$set['status'] = $status;
		$set['updated_at'] = date("Y-m-d H:i:s");
		TZ_Loader::model('Orders', 'Device')->update($set,$condition);
        return array('uid' => $orderInfo['uid'],'tel' =>  $orderInfo['tel']);
    }
    //绑定新卡记录老卡
    public function bindNewCard($orderInfo,$orderDetail)
    {
        $set['oid'] = $orderInfo['oid'];
        $set['uid'] = $orderDetail['uid'];
        $set['telephone'] = $orderDetail['tel'];
        $set['ccid_old'] = $orderInfo['ccid'];
        $set['status'] = 2;
        $set['created_at'] = date('Y-m-d H:i:s');
        $set['updated_at'] = date('Y-m-d H:i:s');
        $res = TZ_Loader::model('CardChange', 'Device')->insert($set);
        return $res;
    }
    //获取是否已换卡
    public function ischanged($ccid)
    {
        $res = TZ_Loader::model('CardChange', 'Device')->select(array('ccid_old:eq'=>$ccid), 'id', 'ROW');
        if($res['id'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
