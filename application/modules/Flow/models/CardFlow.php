<?php

/**
 * CardFlowModel file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class CardFlowModel extends TZ_Db_Table
{

    //init
    public function __construct()
    {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.card_flow');
    }

    public function getCcidList($date)
    {
        $sql = "SELECT ctelephone,ccid,now() as updated_at,now() as created_at,'".$date."' as oper_date  FROM card_flow";
        return $this->_db->query($sql)->fetchAll();
    }

}
