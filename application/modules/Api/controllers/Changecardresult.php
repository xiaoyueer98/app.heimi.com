<?php
/**
 * Payresult controller class
 * @author  nick <zhaozhiwei@747.cn>
 * @final 2014-11-28
 */
class ChangecardresultController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
        $this->_view->display('result_succ.html');
	}
}
