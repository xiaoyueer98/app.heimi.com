<?php

/**
 * @package server_box_boss_web
 * CardCenter  
 * @author mochou<sunyue@747.cn>
 * @copyright 2014-2-10 10:16
 * @version 1.0
 * @since
 */
class CardCenterService {

    public function connectCardCenter() {
        $config = Yaf_Application::app()->getConfig();
        $host = $config->cardcenter->host;
        $port = $config->cardcenter->port;
        $socket = new Thrift\Transport\TSocket($host, $port);
        $socket->setSendTimeout(30000);
        $socket->setRecvTimeout(20000);
        $transport = new Thrift\Transport\TFramedTransport($socket);
        $protocol = new Thrift\Protocol\TBinaryProtocol($transport);

        $client = new Thrift\Server\CardCenterServiceClient($protocol);

        $transport->open();
        return $client;
    }

    //写日志方法
    public function writeLog($type, $detail,$unique_info) {

        $date = date("Y-m-d H:i:s");
        $arError = array(
            'type'		=>	$type,
	    'detail'		=>	$detail,
            'unique_info'       =>	$unique_info,
	    'created_at'=>	$date,
	    'updated_at'=>	$date
	);
        return TZ_Loader::model('CardcenterErrorLog','Flow')->insert($arError);
    }

    /*
     *   同步充值订单表中得数据到卡中心
     *   $data是2维数组  类似array(array('ctelephone'=>'','created_at'=>'')) 
     *      
     */

    public function rechargeToCenter($data,$unique_info) {
        //插入到卡中心
        $client = $this->connectCardCenter();
        $rechargesOb = new Thrift\Server\Recharges();

        $cardsOb = new Thrift\Server\Recharges();
        $cardsOb->telephone = $data['ctelephone'];
        $cardsOb->rechargedAt = $data['created_at']; 
        
        try {
            $re = $client->addRecharge($cardsOb);
        } catch (Exception $e) {
            $detail = $e->getMessage();
            $this->writeLog("1", addslashes($detail),$unique_info);
        }
    }

    //获取卡的基本信息
    public function getCardInfoByCCID($ccid)
    {
        $client = $this->connectCardCenter();
        return $client->searchCardInfoByCCID($ccid);
    }    
    static private function _die($code, $msg) {
        return json_encode(array(
            'code' => $code,
            'msg' => $msg));
    }

}
