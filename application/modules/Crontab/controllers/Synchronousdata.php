<?php

/**
 * @package compent_cellular_data_service
 * Synchronousdata  调取山治的同步数据服务
 * @author mochou<sunyue@747.cn>
 * @copyright 2014-12-24 16:52:20
 * @version 1.0
 * @since
 */
class SynchronousdataController extends Yaf_Controller_Abstract {

    //同步数据，查找另一个数据库中最后一条的记录的添加时间，将本数据库中这个时间之后的数据插入
    public function insertCardFlowAction() {
        
        $host = Yaf_Application::app()->getConfig()->crontab->flow->host;  //获取接口域名
        $date = date("Y-m-d"); //同步当天时间      
        
        
        //查看当天是否已有成功执行的记录,有则不在同步（一天同步一次）
//        $re = TZ_Loader::model('Crontablog', 'Crontab')->select(array("status:eq" => 1, "type:eq" => 1, "created_at:eq" => $date), "id", "ROW");
//        if (!empty($re)) {
//            echo "今天已经同步过数据";
//            die;
//        }


        //查询最后一个手机号添加时间
        $url = $host . "/traffic_find_web/getLastCardTime";
        $cardLastTime = TZ_Loader::service("Data", "Crontab")->getData($url);
        //var_dump($cardLastTime);die;
        if ($cardLastTime == "fail") {
            //处理  插入错误原因为”查询最后一个手机号添加时间失败“
            $arError = array("data" => "查询最后一个手机号添加时间失败", "status" => 0, "created_at" => $date, "updated_at" => $date);
            TZ_Loader::model('Crontablog', 'Crontab')->insert($arError);
            echo "查询最后一个手机号添加时间失败";
            die;
        } else {
            $condition['created_at:gt'] = $cardLastTime;
            //$condition['limit'] = 1;
            $data = TZ_Loader::model('CardFlow', 'Flow')->select($condition, "id,pid,pname,ctelephone,cpid,ccid,operate_name as operateName,start_date as startDate,end_date as endDate,status,updated_at as updatedAt,created_at as createdAt", "ALL");
            //var_dump($data);
            if (empty($data)) {
                //处理成功 插入一条数据
                $arOk = array("data" => "无任何数据更新", "status" => 1, "created_at" => $date, "updated_at" => $date);
                TZ_Loader::model('Crontablog', 'Crontab')->insert($arOk);
                echo "没有最新数据";
                die;
            } else {
                $data = array("card" => $data);
                $json = json_encode($data);
            }
        }
        //var_dump($json);die;  
        

        //调取插入数据接口
        $url1 = $host . "/traffic_find_web/insertCardFlow";
        $dataNew["cards"] = $json;
        $result = TZ_Loader::service("Data", "Crontab")->postData($dataNew, $url1);
        if ($result == "fail") {
           
            //处理失败  插入错误原因为”批量插入手机号失败“
            $arError = array("data" => "批量插入手机号失败", "status" => 0, "created_at" => $date, "updated_at" => $date);
            TZ_Loader::model('Crontablog', 'Crontab')->insert($arError);
            echo "插入数据失败";die;
        
            
        } elseif ($result == "success") {
            
            //处理成功 插入一条数据
            $arOk = array("data" => "数据同步成功", "status" => 1, "created_at" => $date, "updated_at" => $date);
            TZ_Loader::model('Crontablog', 'Crontab')->insert($arOk);
            echo "同步成功";die;
        
            
        }
    }

}
