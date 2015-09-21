<?php

/**
 * CronUserFlowLogModel file
 *

 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class CronUserFlowLogModel extends TZ_Db_Table
{
	private static $_date = '';
	//init


	public function __construct()
	{
		self::$_date = date("Ym");

		parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.user_flow_log_' . self::$_date);
	}
	public function getUserFlowLogList($ccid,$startDate,$endDate, $limit, $size)
	{
		$tableName=$this->_getTableName($startDate);
		$sql="select ctelephone,ccid,start_date,size from ".$tableName;
		$where="  where start_date between '$startDate' and '$endDate'";
		if(!empty($ccid)){
			$where.=" and ccid='$ccid'";
		}
		$where.=" limit $limit, $size";
		return $this->_db->query($sql.$where)->fetchAll();;
	}
	private function _getTableName($date){
		return "virtual_db.user_flow_log_".date('Ym',strtotime($date));
	}
}
