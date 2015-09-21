<?php
/**
 * buyflow controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class GetupgradeController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$params = TZ_Request::getParams('get');
		$tag='';
		if(isset($params['tag'])){
			$tag=$params['tag'];
		}
		$resultData = TZ_loader::service('Upgrade', 'Upgrade')->getUpgrade($tag);
		TZ_Request::success($resultData);
	}	
		
}
