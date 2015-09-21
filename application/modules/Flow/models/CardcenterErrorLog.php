<?php

/**
 * CardcenterErrorLogModel file
 * 
 * @author mochou <sunyue@747.cn>
 * @final 2015-2-27
 */
class CardcenterErrorLogModel extends TZ_Db_Table
{

    //init
    public function __construct()
    {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.cardcenter_error_log');
    }


}
