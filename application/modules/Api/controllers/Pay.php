<?php

/**
 * pay controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class PayController extends Yaf_Controller_Abstract {

    //index
    public function indexAction() {
        
        $sNewCardID = Yaf_Registry::get('config')->newcardid;
        $sOldCardID = Yaf_Registry::get('config')->oldcardid;
        if (!empty($_GET['ccid'])) {
            $ccid = $_GET['ccid'];
        }
        if (strlen($ccid) == 20) {
            $ccid = substr($ccid, 0, 19);
        } else if (strlen($ccid) != 19) {
            throw new Exception("请检查您的ICCID!");
        }
        $cfArr = TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
        if (empty($cfArr)) {

            $this->_view->display("no_info.html");
            die;
            //throw new Exception("该卡不属于747卡!");
        }else{
            $pid = $cfArr['cpid'];
        }
        if (intval($sNewCardID) != $pid && $pid != $sOldCardID)
        {
            $this->_view->display("no_info.html");
        }else if($pid == $sOldCardID){
            header("location:http://app.747.cn");
        }
        else{
            $this->_view->display('pay.html');
        }
    }

}
