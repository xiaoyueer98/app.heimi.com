<?php

/**
 * FlowService class file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-08-08
 */
class FlowService
{

//登录提交的url地址(表单中的action的绝对地址)
    static private $_login_url1 = 'https://uac.10010.com/portal/Service/MallLogin?password=747808&pwdType=01&productType=01&redirectType=01&rememberMe=1&userName=';
    static private $_login_url2 = 'https://uac.10010.com/portal/Service/MallLogin?password=089968&pwdType=01&productType=01&redirectType=01&rememberMe=1&userName=';

    //初始化任务表，$date 任务表需要时间
    public function InitFlowJob($sOperDate)
    {
        //获取ccid列表
        $arCcidList = $this->getCcidList($sOperDate);
        //查看当前日期任务表中是否已经有数据
        $arJobList  = $this->getJobFlowList($sOperDate);
        if (!empty($arJobList) && count($arJobList) > 0)
        {
            return false;
        }
        else
        {
            //插入任务表
            TZ_Loader::model('UserFlowJob', 'Flow')->insert($arCcidList);
            //写入redis管道
            TZ_Loader::service('FlowRedis', 'Crontab')->setCcidRds($arCcidList);
            return true;
        }
    }

    public function getUserFlow($sDate)
    {

        set_time_limit(0);
        ini_set('memory_limit', '128M');
        $arCcidList = $this->getJobFlowList($sDate);
        foreach ($arCcidList as $arCcidInfo)
        {
            $this->getContentListFrom10010($arCcidInfo, $sDate);
        }
        /*
          $isEXEC = TZ_Loader::model('JobExecDate', 'Flow')->select(array("exec_date:eq" => date("Y-m-d")), "*", "ROW");
          if (empty($isEXEC) && count($isEXEC) == 0)
          {

          set_time_limit(0);
          ini_set('memory_limit', '128M');
          $arJobExec  = array("exec_date" => date("Y-m-d"), "created_at" => date("Y-m-d H:i:s"));
          TZ_Loader::model('JobExecDate', 'Flow')->insert($arJobExec);
          $arCcidList = $this->getJobFlowList($sDate);
          foreach ($arCcidList as $arCcidInfo)
          {
          $this->getContentListFrom10010($arCcidInfo, $sDate);
          }
          }
          else
          {
          return false;
          }
         * 
         */
    }

    /**
     * 获取正在使用的ccid
     *
     * @param $telephone string
     * @return bool
     */
    public function getCcidList($date)
    {
        return TZ_Loader::model('CardFlow', 'Flow')->getCcidList($date);
    }

    //获取任务表中待处理的数据
    protected function getJobFlowList($sDate)
    {

        $con = array("status:eq" => 1, "oper_date:eq" => $sDate);

        return TZ_Loader::model('UserFlowJob', 'Flow')->select($con, "*", "ALL");
    }

    public function getContentListFrom10010($arCcidInfo, $sDate)
    {
        $isExitCCID = TZ_Loader::service('FlowRedis', 'Crontab')->isExitCcid($arCcidInfo['ctelephone']);

        if (!$isExitCCID)
        {
            return false;
        }
        $sBeginDate = date("Y-m-d", (strtotime($sDate)));
        $sEndDate   = date("Y-m-d", (strtotime($sDate)));

        //$sBeginDate = "2014-11-01";
        //$sEndDate   = date("Y-m-d", strtotime("1 days ago"));

        $sTelphone = $arCcidInfo['ctelephone'];


        /*         * *************************模拟登陆到联通************************** */
        $cookie_path                    = './cookie';
        $url                            = self::$_login_url1 . $sTelphone;
        $ch                             = curl_init();
        $params[CURLOPT_URL]            = $url;
        $params[CURLOPT_HEADER]         = true;
        $params[CURLOPT_RETURNTRANSFER] = true;
        $params[CURLOPT_FOLLOWLOCATION] = true;
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        $params[CURLOPT_USERAGENT]      = 'Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';
        $params[CURLOPT_POST]           = false;
        if (isset($_COOKIE['cookie_jar']) && ($_COOKIE['cookie_jar'] || is_file($_COOKIE['cookie_jar'])))
        {
            $params[CURLOPT_COOKIEFILE] = $_COOKIE['cookie_jar'];
        }
        else
        {
            $cookie_jar                = tempnam($cookie_path, 'cookie');
            $params[CURLOPT_COOKIEJAR] = $cookie_jar;
            setcookie('cookie_jar', $cookie_jar);
        }
        curl_setopt_array($ch, $params);
        $oLoginSource = curl_exec($ch);

        //判断是否是新卡
        if (strpos($oLoginSource, "用户名或密码不正确"))
        {

            $cookie_path                    = './cookie';
            $url                            = self::$_login_url2 . $sTelphone;
            $ch                             = curl_init();
            $params[CURLOPT_URL]            = $url;
            $params[CURLOPT_HEADER]         = true;
            $params[CURLOPT_RETURNTRANSFER] = true;
            $params[CURLOPT_FOLLOWLOCATION] = true;
            $params[CURLOPT_SSL_VERIFYPEER] = false;
            $params[CURLOPT_SSL_VERIFYHOST] = false;
            $params[CURLOPT_USERAGENT]      = 'Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';
            $params[CURLOPT_POST]           = false;
            if (isset($_COOKIE['cookie_jar']) && ($_COOKIE['cookie_jar'] || is_file($_COOKIE['cookie_jar'])))
            {
                $params[CURLOPT_COOKIEFILE] = $_COOKIE['cookie_jar'];
            }
            else
            {
                $cookie_jar                = tempnam($cookie_path, 'cookie');
                $params[CURLOPT_COOKIEJAR] = $cookie_jar;
                setcookie('cookie_jar', $cookie_jar);
            }
            curl_setopt_array($ch, $params);
            $oLoginSource = curl_exec($ch);
        }
        if (strpos($oLoginSource, "用户名或密码不正确"))
        {
            $arErrorLog               = array();
            $arErrorLog['ccid']       = $arCcidInfo['ccid'];
            $arErrorLog['ctelephone'] = $arCcidInfo['ctelephone'];
            $arErrorLog['type']       = 1;
            $arErrorLog['oper_date']  = $sBeginDate;
            $arErrorLog['data']       = "用户名或密码不正确";
            $arErrorLog['updated_at'] = $arErrorLog['created_at'] = date("Y-m-d H:i:s");
            TZ_Loader::model('FlowErrorLog', 'Flow')->insert($arErrorLog);
        }
        elseif (strpos($oLoginSource, "验证码错误"))
        {
            $arErrorLog               = array();
            $arErrorLog['ccid']       = $arCcidInfo['ccid'];
            $arErrorLog['ctelephone'] = $arCcidInfo['ctelephone'];
            $arErrorLog['type']       = 1;
            $arErrorLog['data']       = "验证码错误";
            $arErrorLog['oper_date']  = $sBeginDate;
            $arErrorLog['updated_at'] = $arErrorLog['created_at'] = date("Y-m-d H:i:s");
            TZ_Loader::model('FlowErrorLog', 'Flow')->insert($arErrorLog);
        }
        /**         * ************************模拟登陆到联通************************** */
        /**         * ************************获取页面详情************************** */
        $sDetailUrl                 = 'http://iservice.10010.com/ehallService/static/queryMonth/execute2/YHgetMonths/QUERY_paramSession.processData/QUERY_paramSession_Data/000100030004/undefined/' . $sBeginDate . '/' . $sEndDate . '/undefined?_=1406183421740&menuid=000100030004';
        $params[CURLOPT_URL]        = $sDetailUrl;
        $params[CURLOPT_POSTFIELDS] = '';
        $params[CURLOPT_POST]       = false;
        curl_setopt_array($ch, $params);
        curl_exec($ch);

        $sDetailUrl2                = 'http://iservice.10010.com/ehallService/static/phoneNetFlow/execute/YH102010014/_QUERY_YH102010014.processData/QueryYH102010014_Data/true/1/500?_=' . time();
        $params[CURLOPT_URL]        = $sDetailUrl2;
        $params[CURLOPT_POSTFIELDS] = '';
        $params[CURLOPT_POST]       = false;
        curl_setopt_array($ch, $params);
        $content                    = curl_exec($ch);

        /**         * ************************获取页面详情************************** */
        //获取html table中的内容
        $arTable = $this->getTable($content);

        //html页面流量列表头
        $arTable1 = $arTable[0];

        $arTable1Td = $this->getTd($arTable1);
        //流量字节字节
        $sByte      = rtrim($arTable1Td[4], "Byte");
        //如果流量字节大于0，那么检索详单
        if ($sByte > 0)
        {
            //判断是否含有“通信地点”关键字
            $isNew      = (strpos($content, "通信地点") == true) ? true : false;
            $arTable2   = $arTable[2];
            $arTable2Td = $this->getTd($arTable2);
            if ($isNew)
            {
                $arTable2TdData = array_chunk($arTable2Td, 5);
            }
            else
            {
                $arTable2TdData = array_chunk($arTable2Td, 4);
            }

            $arFlowLog  = array();
            $arFlowData = array();
            foreach ($arTable2TdData as $arData)
            {

                if ($isNew)
                {
                    //$arFlowLog['uid']        = $arCcidInfo['uid'];
                    //$arFlowLog['telephone']  = $arCcidInfo['telephone'];
                    $arFlowLog['ctelephone'] = $arCcidInfo['ctelephone'];
                    $arFlowLog['ccid']       = $arCcidInfo['ccid'];
                    $arFlowLog['start_date'] = $arData['1'];
                    $arFlowLog['size']       = $arData['2'] * 1024;
                    $arFlowLog['city']       = $arData['3'];
                    $arFlowLog['price']      = $arData['4'];
                    $arFlowLog['updated_at'] = $arFlowLog['created_at'] = date("Y-m-d H:i:s");
                }
                else
                {
                    //$arFlowLog['uid']        = $arCcidInfo['uid'];
                    //$arFlowLog['telephone']  = $arCcidInfo['telephone'];
                    $arFlowLog['ctelephone'] = $arCcidInfo['ctelephone'];
                    $arFlowLog['ccid']       = $arCcidInfo['ccid'];
                    $arFlowLog['start_date'] = $arData['2'];
                    $arFlowLog['size']       = $arData['3'];
                    $arFlowLog['updated_at'] = $arFlowLog['created_at'] = date("Y-m-d H:i:s");
                }

                array_push($arFlowData, $arFlowLog);
            }

            //插入流量详单数据
            TZ_Loader::model('CronUserFlowLog', 'Flow')->insert($arFlowData);
        }
        //修改任务表已经检索的状态
        TZ_Loader::model('UserFlowJob', 'Flow')->update(array("status" => 2, "updated_at" => date("Y-m-d H:i:s")), array("id:eq" => $arCcidInfo['id']));
    }

    public function getTable($content)
    {
        $str = preg_replace("/[\t\n\r]+/", "", $content);
        preg_match_all("/<table[^>]*>.*?(?<!\<\/table>)(?:\d+).*?<\/table>/s", $str, $newcode);
        return $newcode[0];
    }

    public function getTd($table)
    {
        $str = preg_replace("/[\t\n\r]+/", "", $table);
        preg_match_all('/<td[^>]*>(.*?)<\/td[^>]?>/i', $str, $newcode);
        return $newcode[1];
    }

    /**
     * 写入本地日志文件
     * @param string $msg 日志
     * @return void
     * */
    public function wirteLog($msg, $log = "log.txt")
    {

        $file = APP_PATH . "/test/" . $log;

        //chmod($file, 0777);
        $log = fopen($file, 'a+');

        fwrite($log, date('Y-m-d H:i:s') . " : " . $msg . "\r\n");
        fclose($log);
        return true;
    }

    public function reGetUserFlow()
    {
        $arCcidList = TZ_Loader::model('FlowErrorLog', 'Flow')->getReGetFlowList();
        if (!empty($arCcidList) && count($arCcidList) > 0)
        {
            TZ_Loader::service('FlowRedis', 'Crontab')->setCcidRds($arCcidList, "sec");
            foreach ($arCcidList as $cardInfo)
            {
                $this->reGetContentListFrom10010($cardInfo);
            }
        }
    }

    public function reGetContentListFrom10010($arCcidInfo)
    {
        $isExitCCID = TZ_Loader::service('FlowRedis', 'Crontab')->isExitCcid($arCcidInfo['ctelephone'], "sec");

        if (!$isExitCCID)
        {

            return false;
        }
        $sDate      = $arCcidInfo['oper_date'];
        $sBeginDate = date("Y-m-d", (strtotime($sDate)));
        $sEndDate   = date("Y-m-d", (strtotime($sDate)));

        //$sBeginDate = "2014-11-01";
        //$sEndDate   = date("Y-m-d", strtotime("1 days ago"));

        $sTelphone = $arCcidInfo['ctelephone'];


        /*         * *************************模拟登陆到联通************************** */
        $cookie_path                    = './cookie';
        $url                            = self::$_login_url1 . $sTelphone;
        $ch                             = curl_init();
        $params[CURLOPT_URL]            = $url;
        $params[CURLOPT_HEADER]         = true;
        $params[CURLOPT_RETURNTRANSFER] = true;
        $params[CURLOPT_FOLLOWLOCATION] = true;
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        $params[CURLOPT_USERAGENT]      = 'Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';
        $params[CURLOPT_POST]           = false;
        if (isset($_COOKIE['cookie_jar']) && ($_COOKIE['cookie_jar'] || is_file($_COOKIE['cookie_jar'])))
        {
            $params[CURLOPT_COOKIEFILE] = $_COOKIE['cookie_jar'];
        }
        else
        {
            $cookie_jar                = tempnam($cookie_path, 'cookie');
            $params[CURLOPT_COOKIEJAR] = $cookie_jar;
            setcookie('cookie_jar', $cookie_jar);
        }
        curl_setopt_array($ch, $params);
        $oLoginSource = curl_exec($ch);

        //判断是否是新卡
        if (strpos($oLoginSource, "用户名或密码不正确"))
        {

            $cookie_path                    = './cookie';
            $url                            = self::$_login_url2 . $sTelphone;
            $ch                             = curl_init();
            $params[CURLOPT_URL]            = $url;
            $params[CURLOPT_HEADER]         = true;
            $params[CURLOPT_RETURNTRANSFER] = true;
            $params[CURLOPT_FOLLOWLOCATION] = true;
            $params[CURLOPT_SSL_VERIFYPEER] = false;
            $params[CURLOPT_SSL_VERIFYHOST] = false;
            $params[CURLOPT_USERAGENT]      = 'Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1';
            $params[CURLOPT_POST]           = false;
            if (isset($_COOKIE['cookie_jar']) && ($_COOKIE['cookie_jar'] || is_file($_COOKIE['cookie_jar'])))
            {
                $params[CURLOPT_COOKIEFILE] = $_COOKIE['cookie_jar'];
            }
            else
            {
                $cookie_jar                = tempnam($cookie_path, 'cookie');
                $params[CURLOPT_COOKIEJAR] = $cookie_jar;
                setcookie('cookie_jar', $cookie_jar);
            }
            curl_setopt_array($ch, $params);
            $oLoginSource = curl_exec($ch);
        }

        if (strpos($oLoginSource, "验证码错误") || strpos($oLoginSource, "用户名或密码不正确") || strpos($oLoginSource, "请稍后再试"))
        {
            $set                 = array();
            $set['status']       = 3;
            $set['updated_at']   = date("Y-m-d H:i:s");
            $con                 = array();
            $con['oper_date:eq'] = $arCcidInfo['oper_date'];
            $con['ccid:eq']      = $arCcidInfo['ccid'];
            $con['type:eq']      = 1;
            TZ_Loader::model('FlowErrorLog', 'Flow')->update($set, $con);
            return;
        }
        /**         * ************************模拟登陆到联通************************** */
        /**         * ************************获取页面详情************************** */
        $sDetailUrl                 = 'http://iservice.10010.com/ehallService/static/queryMonth/execute2/YHgetMonths/QUERY_paramSession.processData/QUERY_paramSession_Data/000100030004/undefined/' . $sBeginDate . '/' . $sEndDate . '/undefined?_=1406183421740&menuid=000100030004';
        $params[CURLOPT_URL]        = $sDetailUrl;
        $params[CURLOPT_POSTFIELDS] = '';
        $params[CURLOPT_POST]       = false;
        curl_setopt_array($ch, $params);
        curl_exec($ch);

        $sDetailUrl2                = 'http://iservice.10010.com/ehallService/static/phoneNetFlow/execute/YH102010014/_QUERY_YH102010014.processData/QueryYH102010014_Data/true/1/500?_=' . time();
        $params[CURLOPT_URL]        = $sDetailUrl2;
        $params[CURLOPT_POSTFIELDS] = '';
        $params[CURLOPT_POST]       = false;
        curl_setopt_array($ch, $params);
        $content                    = curl_exec($ch);

        /**         * ************************获取页面详情************************** */
        //获取html table中的内容
        $arTable = $this->getTable($content);

        //html页面流量列表头
        $arTable1 = $arTable[0];

        $arTable1Td = $this->getTd($arTable1);
        //流量字节字节
        $sByte      = rtrim($arTable1Td[4], "Byte");
        //如果流量字节大于0，那么检索详单
        if ($sByte > 0)
        {
            //判断是否含有“通信地点”关键字
            $isNew      = (strpos($content, "通信地点") == true) ? true : false;
            $arTable2   = $arTable[2];
            $arTable2Td = $this->getTd($arTable2);
            if ($isNew)
            {
                $arTable2TdData = array_chunk($arTable2Td, 5);
            }
            else
            {
                $arTable2TdData = array_chunk($arTable2Td, 4);
            }

            $arFlowLog  = array();
            $arFlowData = array();
            foreach ($arTable2TdData as $arData)
            {

                if ($isNew)
                {
                    //$arFlowLog['uid']        = $arCcidInfo['uid'];
                    //$arFlowLog['telephone']  = $arCcidInfo['telephone'];
                    $arFlowLog['ctelephone'] = $arCcidInfo['ctelephone'];
                    $arFlowLog['ccid']       = $arCcidInfo['ccid'];
                    $arFlowLog['start_date'] = $arData['1'];
                    $arFlowLog['size']       = $arData['2'] * 1024;
                    $arFlowLog['city']       = $arData['3'];
                    $arFlowLog['price']      = $arData['4'];
                    $arFlowLog['updated_at'] = $arFlowLog['created_at'] = date("Y-m-d H:i:s");
                }
                else
                {
                    //$arFlowLog['uid']        = $arCcidInfo['uid'];
                    //$arFlowLog['telephone']  = $arCcidInfo['telephone'];
                    $arFlowLog['ctelephone'] = $arCcidInfo['ctelephone'];
                    $arFlowLog['ccid']       = $arCcidInfo['ccid'];
                    $arFlowLog['start_date'] = $arData['2'];
                    $arFlowLog['size']       = $arData['3'];
                    $arFlowLog['updated_at'] = $arFlowLog['created_at'] = date("Y-m-d H:i:s");
                }

                array_push($arFlowData, $arFlowLog);
            }

            //插入流量详单数据
            TZ_Loader::model('CronUserFlowLog', 'Flow')->insert($arFlowData);
        }
        //修改失败的数据状态
        $con                 = array();
        $con['oper_date:eq'] = $arCcidInfo['oper_date'];
        $con['ccid:eq']      = $arCcidInfo['ccid'];
        $con['type:eq']      = 1;
        TZ_Loader::model('FlowErrorLog', 'Flow')->update(array("status" => 2, "updated_at" => date("Y-m-d H:i:s")), $con);
    }

}
