<?php
/**
 * order service file
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class UpgradeService
{	
	/**
	 * 升级检测
	 * 
	 * @param 操作系统 $os
	 * @param 应用名称 $appName
	 */
	public function getUpgrade($tag)
	{
		if(!empty($tag))
        {
			$condition['tag:eq']=$tag;
		}
        else
        {
            $condition['tag:eq']='box';
        }
		$condition['status:eq']=1;
		$condition['limit'] = "0,1";
        $condition['order'] = 'created_at desc';
		return TZ_loader::model('UpgradeApps')->select($condition, '*', 'ALL');
	}
    //获取某产品最新版本
	public function getProUpgrade($name,$version='')
    {
        if(!empty($name))
        {
			$condition['tag:eq'] = $name;
            $condition['order'] = 'created_at desc';
            if($version)
            {
                $condition['version:eq'] = $version;
            }
            else
            {
                $condition['status:eq'] = 1;
            }
		}
        else
        {
            return false;
        }
		return TZ_loader::model('UpgradeApps')->select($condition, '*', 'ROW');
    }
}