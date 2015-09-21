<?php
/**
 * Active controller class
 *
 * @author  mochou <sunyue@747.cn>
 * @final 2014-11-01
 */
class ActiveController extends Yaf_Controller_Abstract
{
	//index
	public function indexAction()
	{
		$this->_view->display('active.html');
	}	
		
}
