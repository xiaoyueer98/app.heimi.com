<?php
/**
 * paywork controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-29
 */
class PayworkController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		TZ_loader::service('PayWork', 'Api')->setWork();
		TZ_Request::success();
	}	
		
}
