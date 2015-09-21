<?php
/**
 * cardinfo controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class CardinfoController extends Yaf_Controller_Abstract
{
    //index
    public function indexAction()
    {
        $sNewCardID = Yaf_Registry::get('config')->newcardid;   //4
        $scardID3g = Yaf_Registry::get('config')->cardid3g;     //7
        $sz3g = Yaf_Registry::get('config')->sz3g;     //7

        if (!empty($_GET['ccid']))
        {
            $ccid = $_GET['ccid'];
        }
        if (strlen($ccid) == 20)
        {
            $ccid = substr($ccid, 0, 19);
        }
        else if (strlen($ccid) != 19)
        {
            throw new Exception("请检查您的ICCID!");
        }
        //获取卡信息
        $cfArr = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
        if (!empty($cfArr))
        {
            $pid = $cfArr['cpid'];
        }
        else
        {
            $this->_view->display("no_info.html");
            exit;
        }
        //3.5G
        if (intval($sNewCardID) == $pid)
        {
            //$this->_view->display("card_info_year.html");
            $this->_view->display("card_info_new.html");
        }
        elseif (intval($scardID3g) == $pid  or $pid==$sz3g)
        {
            $this->_view->display("card_info_3G.html");
        }
        //深圳区分本地、全国
        elseif($pid == '8')
        {
            $this->_view->display("card_info_shenzhen.html");
        }
        else
        {
            //$this->_view->display("card_info.html");
            $this->_view->display("card_info_old.html");
        }
    }
    //网路繁忙
    public function netbusyAction()
    {
        $this->_view->display("netbusy.html");
    }

    public function getcardinfoAction()
    {

        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            $result = TZ_Loader::service('UserFlow', 'Flow')->getProductCardInfo($ccid);

            TZ_Request::success($result);
        }
        else
        {
            throw new Exception("参数错误");
        }
    }

    public function getcardinfoyearAction()
    {
        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            $result = TZ_Loader::service('UserFlow', 'Flow')->getProductCardInfoYear($ccid);
            TZ_Request::success($result);
        }
        else
        {
            throw new Exception("参数错误");
        }
    }

    public function getflowyearAction()
    {

        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            //得到卡的相关信息。主要是ctelephone
            $data = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
            $ctelephone = $data['ctelephone'];
            //获取流量详情
            $rep = TZ_Loader::service('Flow', 'Flow')->getSurplusFlow($ctelephone);
            //流量处理
            $result = array('left' => $rep['left'] . 'MB', 'total' => $rep['total'] . 'MB');
            if ($rep['total'] > 0)
            {
                $percent = ($rep['left'] / $rep['total']) * 100;
            }
            else
            {
                $percent = '0';
            }
            //对百分比大于100做处理
            ($percent > 100) AND $percent = 100 OR $percent = $percent;
            $result['percent'] = $percent . '%';
            TZ_Request::success($result);
        }
        else
        {
            throw new Exception("参数错误");
        }
    }
    //获取流量套餐剩余详情
    public function getflownewAction()
    {
        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];

        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            //得到卡的相关信息。主要是ctelephone
            $data = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
            $ctelephone = $data['ctelephone'];
            //获取流量详情
            $rep = TZ_Loader::service('Flow', 'Flow')->getSurplusFlow($ctelephone);
            if (!empty($rep))
            {
                //流量处理
                $result = array('left' => $rep['left'], 'total' => $rep['total']);
                if ($rep['total'] > 0)
                {
                    $percent = $rep['left'] / $rep['total'];
                }
                else
                {
                    $percent = '0';
                }
                //对百分比大于100做处理
                $percent = ($percent < 0) ? 0 : $percent;
                $result['percent'] = $percent;
                TZ_Request::success($result);
            }
            else
            {
                $detail = "获取失败";
                TZ_Request::error($detail, "500");
            }
        }
        else
        {
            throw new Exception("参数错误");
        }
    }
    //获取剩余流量区分本地和全国
    public function getflowdifferAction()
    {
        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            //得到卡的相关信息。主要是ctelephone
            $data = (array)TZ_Loader::service('UserFlow', 'Flow')->getFlowProductId($ccid);
            $ctelephone = $data['ctelephone'];
            //获取流量详情
            $rep = TZ_Loader::service('Flow', 'Flow')->getSurplusFlow($ctelephone,true);
            if (!empty($rep))
            {
                //流量处理
                $result = array('unlimit' => $rep['unlimit'], 'local' => $rep['local'],'punlimit' => 0,'plocal' => 0);
                if($result['unlimit'] > 0)
                {
                    $result['punlimit'] = sprintf($rep['unlimit']/2048)*100;
                }
                if($result['local'] > 0)
                {
                    $result['plocal']   = sprintf($rep['local']/8192)*100;
                }
                if($result['unlimit'] > 100) $result['unlimit'] = floor ($result['unlimit']);
                if($result['local'] > 100) $result['local'] = floor ($result['local']); 
                TZ_Request::success($result);
            }
            else
            {
                $detail = "获取失败";
                TZ_Request::error($detail, "500");
            }
        }
        else
        {
            throw new Exception("参数错误");
        }
    }
    public function getFlowInfoAction()
    {

        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        if ($ccid > 0)
        {
            if (strlen($ccid) == 20)
            {
                $ccid = substr($ccid, 0, 19);
            }
            elseif (strlen($ccid) != 19)
            {
                throw new Exception("您输入的ICCID有误，请检查");
            }
            $result = TZ_Loader::service('UserFlow', 'Flow')->getCurrentFlowInfo($ccid);
            //var_dump($result);
            TZ_Request::success($result);
        }
        else
        {
            throw new Exception("参数错误");
        }
    }

    //新卡（3.5 G）展示
    public function displaynewAction()
    {
        $this->_view->display("card_display_new.html");
    }

    //是否已更换新卡
    public function ischangeAction()
    {
        $params = TZ_Request::getParams('get');
        $ccid = $params['ccid'];
        $result = TZ_Loader::service('Changecardorder', 'Flow')->ischanged($ccid);
        if ($result)
            die("ok");
        else
            die("no");
    }
}