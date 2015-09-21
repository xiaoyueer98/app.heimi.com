<?php

/**
 * FlowErrorLogModel file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class FlowErrorLogModel extends TZ_Db_Table
{

    //init
    public function __construct()
    {
        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.flow_error_log');
    }

    public function getReGetFlowList()
    {
        $sOperDate = date("Y-m-d", strtotime("1 days ago"));
        $sql       = "SELECT oper_date,ccid,ctelephone FROM flow_error_log WHERE status=1 AND type=1 and  oper_date ='$sOperDate' group by ccid";
        return $this->_db->query($sql)->fetchALL();
    }

}
