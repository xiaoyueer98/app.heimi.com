<?php
/**
 * Blacklist model class file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-08-18
 */
class BlacklistModel extends TZ_Db_Table {
    
    /**
     * User blacklist pre key
     *
     * @var string
     */
    private $_key = 'user:blacklist';
	private $_black='user:black:';
    /**
     * Redis object
     *
     * @var object
     */
    private $_redis = null;


    /**
     * Construct object
     *
     * @return void
     */
    public function __construct() {
        parent::__construct(
            Yaf_Registry::get('user_db'), 
            'user_db.blacklist', 
            'telephone'
        );
//		$this->_redis = TZ_Redis::connect('user');
//		$this->_init(); 
    }
    
    /**
     * In blacklist
     *
     * @param string $telephone
     * @return bool
     */
    public function inList($telephone) {
        //return $this->_redis->sIsMember($this->_key, $telephone); 
        return TZ_Redis::connect('black')->get($this->_black.$telephone);
    } 
    public function _getBlackListFromDbByTelephone($telephone){
        $sql = "select * from `user_db`.`blacklist` b";
        $sql .= " where b.`telephone`='{$telephone}'";
        return $this->_db->query($sql)->fetchRow();
    }


    /**
     * Init blacklist
     *
     * @return void
     */
    private function _init() {
        $memberCount = $this->_redis->sCard($this->_key);
        if ($memberCount == 0) {
            $blackList = $this->select(array(), 'telephone');
            if (!empty($blackList)) {
                foreach ($blackList as $rows) {
                    $this->_redis->sAdd($this->_key, $rows['telephone']); 
                } 
            }
        }
    }
}
