<?php

/**
 * PayPlanModel file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class PayPlanModel extends TZ_Db_Table
{

    //init
    public function __construct()
    {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.flow_plan');
    }

    public function getUserPlanList($date)
    {

        $sql = "SELECT id as payid,uid,telephone,ctelephone,fid,fname,ccid,pay_stime,pay_etime,pay_size,pay_money,now() as updated_at,now() as created_at" .
                " FROM flow_plan WHERE pay_stime ='$date' ORDER BY created_at ASC";
        return $this->_db->query($sql)->fetchALL();
    }

}
