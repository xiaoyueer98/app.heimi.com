<?php

/**
 * UserCardModel file
 * 
 * @author basa <wanghaiyu@747.cn>
 * @final 2014-10-15
 */
class UserCardModel extends TZ_Db_Table {

    //init
    public function __construct()
    {

        parent::__construct(Yaf_Registry::get('virtual_db'), 'virtual_db.user_card');
    }

    //获取用户流量卡
    public function getUserCardList($uid)
    {
        $sql = "select status,ccid,cpid FROM user_card WHERE uid='$uid'";
        return $this->_db->query($sql)->fetchAll();
    }

}
