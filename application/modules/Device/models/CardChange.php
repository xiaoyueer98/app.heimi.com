<?php
/**
 * OrdersModel file
 * @author nick <zhaozhiwei@747.cn>
 * @final 2014-11-28
 */
class CardChangeModel extends TZ_Db_Table {

    //init
    public function __construct() {

        parent::__construct(Yaf_Registry::get('device_db'), 'device_db.card_change');
    }

}