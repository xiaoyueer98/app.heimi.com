<?php
/**
 * order service file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class OrderService {

	/**
	 * 购买流量
	 * @param string $uid  用户id
	 * @param string $fid	流量id
	 * @param string $ccid	卡id
	 * @param string $starttime 生效开始时间
	 */
	public function buyFlow($uid,$fid,$ccid,$starttime,$endtime){
		//查询用户信息
                if($uid != 0){
                    $userInfo = TZ_Loader::service('User', 'User')->getInfoByUid($uid);
                }else{
                    $userInfo = array('uid'=>'0','telephone'=>'');
                }
		//print_r($userInfo);
		//查询流量信息
		$flowInfo=TZ_Loader::model('FlowType','Device')->select(array('id:eq'=>$fid), '*', 'ROW');
		//print_r($flowInfo);
		if(!isset($flowInfo['fid'])){
			throw new Exception('对不起当前流量包有问题，请联系客服。');
		}
		//查询当前卡的信息
		$cardInfo = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);;
		//print_r($cardInfo);
		if(!isset($cardInfo['ccid'])){
			throw new Exception('对不起当前卡有问题，请联系客服。');
		}
		//插入流量订单表
		$condition=array();
		$idManager = TZ_Loader::service('IdManager', 'User');
		$oid=$idManager->createUID();
		$condition['order_id']=$oid;
		$condition['uid']=$userInfo['uid'];
		$condition['telephone']=$userInfo['telephone'];
		$condition['ctelephone']=$cardInfo['ctelephone'];
		$condition['ccid']=$cardInfo['ccid'];
		$condition['cpid']=$cardInfo['cpid'];
		$condition['fid']=$flowInfo['id'];
		$condition['fname']=$flowInfo['name'];
		$condition['num']=1;
		$condition['start_date']=$starttime;
		$condition['end_date']=$endtime;
		$condition['user_start_date']=$starttime;
		$condition['user_end_date']=$endtime;
		$condition['status']=1;
		$condition['created_at'] = $condition['updated_at'] = date('Y-m-d H:i:s');
		TZ_Loader::model('FlowOrder','Flow')->insert($condition);

		//调用支付接口
		$result=TZ_Loader::service('Pay','Device')->pay($uid,$oid,$flowInfo['app_price'],$ccid);
		return $result;
	}
	
	/**
	 * 购买3.5G卡
	 * @param string $uid  用户id
	 * @param string $fid	流量id
	 * @param string $ccid	卡id
	 * @param string $starttime 生效开始时间
	 */
	public function buyCard($uid,$fid,$ccid,$starttime,$endtime,$session_id){
		//查询用户信息
		$userInfo = TZ_Loader::service('User', 'User')->getInfoByUid($uid);
		//查询当前卡的信息
		$cardInfo = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
		//print_r($cardInfo);
		if(!isset($cardInfo['ccid'])){
			throw new Exception('对不起当前卡有问题，请联系客服。');
		}

		// 开启事务
		$deviceDb = Yaf_Registry::get('device_db');
		$deviceDb->transBegin();	
		//获得用户收货信息
		$userAddress = TZ_Loader::model('Address','Device')->select(array('uid:eq'=>$uid),'*','ROW');
		//插入硬件订单表
		$condition=array();
		$idManager = TZ_Loader::service('IdManager', 'User');
		$oid=$idManager->createUID();
		//$oid='123'.time();
		$condition['oid']=$oid;
		$condition['uid']=$userInfo['uid'];
		$condition['num']=1;
		$condition['tel']=$userAddress['receive_tel'];
		$condition['name']=$userAddress['name'];
		$condition['address']=$userAddress['province'].$userAddress['city'].$userAddress['area'].$userAddress['detail'];
		$condition['mail_code']=$userAddress['mailCode'];
		$condition['pay_from']=2;
		$condition['status']=1;
		$condition['created_at'] = $condition['updated_at'] = date('Y-m-d H:i:s');
		$addOrder = TZ_Loader::model('Orders','Device')->insert($condition);
		if(empty($addOrder))
		{
			$deviceDb->rollback();
			throw New Exception("当然无法支付，请过后重试！");
		}
		//首次换卡便宜
		$newPrice = 119.000;
		//插入订单详情表
		$ddOrder = array(
			'oid'		=>	$oid,
			'pid'		=>	4,
			'pname'		=>	'3.5GB全国流量卡',
			'fid'		=>	3,
			'fname'		=>	'重庆联通3.5G流量',
			'pprice'	=>	$newPrice,
			'pnum'		=>	1,
			'ccid'		=>	$ccid,
			'type'		=>	2,
			'created_at'=>	date('Y-m-d H:i:s'),
			'updated_at'=>	date('Y-m-d H:i:s')
		);
		$insddOrder = TZ_Loader::model('OrderDetails','Device')->insert($ddOrder);	
		if(empty($insddOrder))
		{
			$deviceDb->rollback();
			throw New Exception("当然无法支付，请过后重试！");
		}
		$dfOrder = array(
			'oid'		=>	$oid,
			'pid'		=>	11,
			'pname'		=>	'3.5GB全国流量',
			'fid'		=>	3,
			'fname'		=>	'重庆联通3.5G流量',
			'pnum'		=>	1,
			'ccid'		=>	$ccid,
			'type'		=>	3,
			'created_at'=>	date('Y-m-d H:i:s'),
			'updated_at'=>	date('Y-m-d H:i:s')
		);
		$insdfOrder = TZ_Loader::model('OrderDetails','Device')->insert($dfOrder);	
		if(empty($insdfOrder))
		{
			$deviceDb->rollback();
			throw New Exception("当然无法支付，请过后重试！");
		}else
		{
			$deviceDb->commit();
		}
		//调用支付接口
		$result=TZ_Loader::service('Pay','Device')->payon($uid,$oid,$newPrice,$ccid,$session_id);
		return $result;
	}
	/**
	 * 支付宝返回操作
	 * @param string $orderId
	 * @param string $status
	 */
	public function setOrder($orderId,$status){
		//查询订单信息
		$orderInfo=TZ_Loader::model('FlowOrder','Flow')->select(array('order_id:eq'=>$orderId), '*', 'ROW');
		if(!isset($orderInfo['order_id'])){
			throw new Exception('对不起当前订单有问题，请联系客服。');
		}
		if($orderInfo['status']==2){
			return true;
		}
		//更新订单状态
		$condition=$set=array();
		$condition['order_id:eq']=$orderId;
		$set['status']=2;
		$set['updated_at']=date("Y-m-d H:i:s");
		$reOrder = TZ_Loader::model('FlowOrder', 'Flow')->update($set,$condition);
                
                if(isset($reOrder) && $reOrder == '1'){
                   
                    //同步到卡中心操作
                    $arFlowOrder = TZ_Loader::model('FlowOrder', 'Flow')->select($condition,"ctelephone,created_at","ROW");
                    try{
                        TZ_Loader::service('CardCenter', 'Flow')->rechargeToCenter($arFlowOrder,$orderId);
                    } catch (Exception $e)
                    {
                        $detail = $e->getMessage();
                        TZ_Loader::service('CardCenter', 'Flow')->writeLog("1", addslashes($detail),$orderId);
                    }
                }
//		//插入充值计划
//		$startTime=$orderInfo['start_date'];
//		$endTime=$orderInfo['end_date'];
//		$month=(date("Y",strtotime($endTime))-date("Y",strtotime($startTime)))*12+date("m",strtotime($endTime))-date("m",strtotime($startTime))+1;
//		//查询流量信息
//		$flowInfo=TZ_Loader::model('FlowType','Device')->select(array('id:eq'=>$orderInfo['fid']), '*', 'ROW');
//		//查询流量包信息
//		$packageInfo=TZ_Loader::model('Package','Flow')->select(array('id:eq'=>$flowInfo['fid']), '*', 'ROW');
//			
//		$plan=array();
//		$plan['uid']=$orderInfo['uid'];
//		$plan['telephone']=$orderInfo['telephone'];
//		$plan['ctelephone']=$orderInfo['ctelephone'];
//		$plan['ccid']=$orderInfo['ccid'];
//		$plan['fid']=$orderInfo['fid'];
//		$plan['fname']=$orderInfo['fname'];
//		$plan['pay_stime']=$startTime;
//		$plan['pay_etime']=date("Y-m-d", strtotime("-1 days",strtotime("+4  months", strtotime($startTime))));
//		$plan['pay_size']=$packageInfo['size'];
//		$plan['pay_money']=$packageInfo['price'];
//		$plan['created_at'] = $plan['updated_at'] = date('Y-m-d H:i:s');
//		TZ_Loader::model('PayPlan', 'Flow')->insert($plan);
//		//一次只能充值三个月，所以判断需要充值几次
//		$times=$month/3;
//		if($times>1){
//			for($i=0;$i<$times-1;$i++){
//				$n                 = $i * 3;
//                $en                = $i * 3 + 3;
//                $payStartTime      = date("Y-m", strtotime("+$n months", strtotime($startTime))) . "-01";
//                $payendTime        = date("Y-m-d", strtotime("-1 days", strtotime("+$en months", strtotime($startTime))));
//				$plan['pay_stime']=$payStartTime;
//				$plan['pay_etime']=$payendTime;
//				TZ_Loader::model('PayPlan', 'Flow')->insert($plan);
//			}
//		}
		return true;
	}
    //根据订单获取订单详细内容
    public function getOrderDetail($orderId)
    {
        $orderDetail = TZ_Loader::model('OrderDetails','Device')->select(array('oid:eq'=>$orderId), '*', 'ROW');
		if(!isset($orderDetail['oid']))
        {
            die("Order is NULL");
        }
        return $orderDetail;
    }
    
}
