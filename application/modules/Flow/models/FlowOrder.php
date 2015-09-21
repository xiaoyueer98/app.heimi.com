<?php

/**
 * FlowOrderModel file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class FlowOrderModel extends TZ_Db_Table {

    //init
    public function __construct() {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.flow_orders');
    }

    public function getCcidList() {
        $sql = "SELECT uid,ctelephone,telephone,ccid,now() as updated_at,now() as created_at,date_format(now(),'%y-%m-%d') as oper_date FROM virtual_db.flow_orders WHERE order_id>0 AND end_date>now() AND status=2";
        return $this->_db->query($sql)->fetchAll();
    }
     public function getfFLowOrdersAll($iccid) {
        $date = date("Y-m-d H:i:s");
        $sql = "SELECT fid  from  virtual_db.flow_orders  WHERE ccid='".$iccid."' AND status=2 AND start_date<='".$date."' AND end_date>='".$date."'  AND (type=2 or (type=1 AND is_charge=1))";
        return $this->_db->query($sql)->fetchAll();
    }

}
