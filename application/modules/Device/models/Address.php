<?php
/**
 * ProductModel file
 * @author 刑天 <wangtongmeng@747.cn>
 * @final 2014-10-21
 */
class AddressModel extends TZ_Db_Table {

    //init
    public function __construct() {

        parent::__construct(Yaf_Registry::get('device_db'), 'device_db.address');
    }

}
