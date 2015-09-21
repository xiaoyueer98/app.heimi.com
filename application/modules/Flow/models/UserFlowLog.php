<?php

/**
 * UserCardlogModel file
 * 

 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class UserFlowLogModel extends TZ_Db_Table
{

    //init


    public function __construct($dateYM)
    {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.user_flow_log_' . $dateYM);
    }

}
