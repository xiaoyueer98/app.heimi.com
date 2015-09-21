<?php
/**
 * buyflow controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class GetflowlogController extends Yaf_Controller_Abstract {

	//用户实时查询剩余流量接口
	public function indexAction() {

		$params=$_GET;
		if (empty($params['iccid'])) {
			throw new Exception("参数错误");
		} else {
			$iccid = $params['iccid'];
		}
		if (!empty($params['start_time']) && !empty($params['end_time']))
		{
			$start=date('Ym',strtotime($params['start_time']));
			$end=date('Ym',strtotime($params['end_time']));
			if($start!=$end){
				throw new Exception("不能跨月查询");
			}
			$startDate=$params['start_time']. " 00:00:00";
			$endDate=$params['end_time']. " 23:59:59";
		}else{
			$startDate=date('Y-m').'-01'. " 00:00:00";
			$endDate=date('Y-m-d'). " 23:59:59";
		}
		if (empty($params['page'])) {
			$page =1;
		} else {
			if(!is_numeric($params['page']))
			throw new Exception('页数格式错误.');
			$page = intval($params['page']);
		}

		if (empty($params['count'])) {
			$count = 10;
		} else {
			if(!is_numeric($params['count']) || $params['count']>100 )
			throw new Exception('页数据量格式错误.');
			$count = intval($params['count']);
		}
		$limit= ($page - 1) * $count;

		$UserFlowLog =  TZ_Loader::service('UserFlowLog', 'Flow')->getUserFlowLogList($iccid,$startDate,$endDate, $limit, $count);

		TZ_Request::success($UserFlowLog);
	}

}
