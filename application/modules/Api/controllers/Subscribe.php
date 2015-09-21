<?php
/**
 * cardinfo controller class
 * @author  octopus <zhangguipo@747.cn>
 * @final 2015-02-11
 */
class SubscribeController extends Yaf_Controller_Abstract
{
    //短信订阅
    public function indexAction()
    {
        $session_id = trim($_POST['session_id']);
        $iccid      = trim($_POST['iccid']);
        $flag       = trim($_POST['flag']);
        if($session_id != '' && $flag != '')
        {
            $uid = TZ_Loader::service('SessionManager','User')->getUid($session_id);
            if(empty($uid))
            {
                TZ_Request::error('登录过期');
                exit;
            }
            else
            {
                $iccid = trim($iccid,',');  
                $arrIccid = explode(',',$iccid);
                if($flag == 'off')      $set = array('status' => 0);
                elseif($flag == 'on')   $set = array('status' => 1);
                else
                {
                    TZ_Request::error('无效参数');
                    exit;
                }
                $conditions = array('uid:eq' => $uid);
                foreach($arrIccid AS $val)
                {
                    if($val)
                    {
                        $conditions['ccid:eq'] = $val;
                    }
                    $row = TZ_Loader::model('UserSubscription','User')->select($conditions,'id','ROW');
                    if($row['id'])
                    {
                        TZ_Loader::model('UserSubscription','User')->update($set,$conditions);
                    }
                    else
                    {
                        $upUser = TZ_Loader::model('UserCard','User')->select($conditions,'*','ALL');
                        if($upUser)
                        {
                            foreach($upUser AS $v)
                            {
                                $cols = array();
                                $cols['user_card_id'] = $v['id'];
                                $cols['uid'] = $v['uid'];
                                $cols['ccid'] = $v['ccid'];
                                $cols['telephone'] = $v['telephone'];
                                $cols['ctelephone'] = $v['ctelephone'];
                                $cols['messages_id'] = 0;
                                $cols['status'] = $set['status'];
                                $cols['created_at'] = $cols['updated_at'] = date('Y-m-d H:i:s');
                                TZ_Loader::model('UserSubscription','User')->insert($cols);
                            }
                        }
                    }
                }
                TZ_Request::success('ok');
                exit;
            }
        }
        else
        {
            TZ_Request::error('无效参数');
            exit;
        }
    }
}