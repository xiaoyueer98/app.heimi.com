<?php

/**
 * UserFlowJob file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class UserFlowJobModel extends TZ_Db_Table {

    //init
    public function __construct() {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.user_flow_job');
    }

}
