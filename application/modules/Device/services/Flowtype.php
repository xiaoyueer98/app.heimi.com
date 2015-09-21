<?php

/** 流量产品
 * @package compent_cellular_data_service
 * Product
 * @author mochou<mochou@747.cn>
 * @copyright 2014-10-28 18:30:29
 * @version 1.0
 * @since
 */
class FlowtypeService {

    

    /**
     * 获取可用产品列表
     * @param  int $type 产品类型 1,盒子，2,卡 0 全部
     * @return array
     */
    public function getFlowtypeList($condition)
    {
        $arFlowtypeList      = TZ_Loader::model('FlowType', 'Device')->select($condition, '*', 'ROW');
        return $arFlowtypeList;
    }

  
}
