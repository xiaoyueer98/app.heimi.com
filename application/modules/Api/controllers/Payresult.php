<?php
/**
 * Payresult controller class
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2014-10-28
 */
class PayresultController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$this->_view->display('result.html');
	}	
		
}