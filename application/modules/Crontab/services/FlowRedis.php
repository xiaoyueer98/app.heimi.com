<?php

/**
 * @package compent_cellular_data_service
 * FlowRedis
 * @author Barce<wanghaiyu@747.cn>
 * @copyright 2014-11-14 12:03:57
 * @version 1.0
 * @since
 */
class FlowRedisService
{

    private $_CCIDSource        = null;
    static private $_ccidlist   = "ccidlist:";
    static private $_reccidlist = "reccidlist:";

    public function __construct()
    {
        $this->_CCIDSource = TZ_Redis::connect('ccidlist');
    }

    //设置到联通获取数据的ccid列表
    public function setCcidRds($arCcidList, $type = 'first')
    {
        if (!empty($arCcidList))
        {
            foreach ($arCcidList as $arCcidInfo)
            {
                if ($type == "first")
                    $this->_CCIDSource->sadd(self::$_ccidlist, $arCcidInfo['ctelephone']);
                else
                    $this->_CCIDSource->sadd(self::$_reccidlist, $arCcidInfo['ctelephone']);
            }
            return true;
        }
        return false;
    }

    //判断某个值是否存在于redis列表
    public function isExitCcid($sCtelephone, $type = 'first')
    {
        if ($type == "first")
            return $this->_CCIDSource->sremove(self::$_ccidlist, $sCtelephone);
        else
            return $this->_CCIDSource->sremove(self::$_reccidlist, $sCtelephone);
    }

}
