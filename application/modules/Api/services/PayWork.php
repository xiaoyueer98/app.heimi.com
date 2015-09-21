<?php
/**
 * paywork service file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-29
 */
class PayWorkService
{
	/**
	 * 开始查询充值计划，生成充值方案
	 */
	public function setWork(){
		//查询要充值的列表
		$condition=array();
		$tomorrow=date("Y-m-d", strtotime("+1 days",time()));
		$condition['pay_stime:between'] = array($tomorrow." 00:00:00", $tomorrow . " 23:59:59");
		$payList=TZ_Loader::model('FlowPlan','Flow')->select($condition,'*','ALL');
		foreach ($payList as $pay){
			$payid=$pay['id'];
			unset($pay['id']);
			$pay['payid']=$payid;
			$pay['created_at']=$pay['updated_at']=date('Y-m-d H:i:s');
			$payLog[]=$pay;
		}
		if(!empty($payLog)){
			TZ_Loader::model('FlowLog','Flow')->insert($payList);
		}
		return true;
	}
}