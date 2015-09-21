<?php
/**
 * bootstrap file
 * 
 * @author octopus <zhangguipo@747.cn>
 * @final 2014-10-20
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{
	/**
	 * data
	 */
	private $_config = null;

	/**
	 * config init
	 */
	public function _initConfig()
	{
		$this->_config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config', $this->_config);
	}
	
	/**
	 * loader config
	 */
	public function _initLoader()
	{
		$loader = new TZ_Loader;
		Yaf_Registry::set('loader', $loader);
	}
	

   /**
    * plug config
    */
	public function _initPlugin(Yaf_Dispatcher $dispatcher)
	{
		$routerPlugin = new RouterPlugin();
		$dispatcher->registerPlugin($routerPlugin);
    }

   /**
    * db config
    */
	public function _initDb()
	{
				//user_db
		$userDb = $this->_config->database->user;
		$userMaster = $userDb->master->toArray();
		$userSlave = !empty($userDb->slave) ? $userDb->slave->toArray() : null;
		$userDb = new TZ_Db($userMaster, $userSlave, $userDb->driver);
		Yaf_Registry::set('user_db', $userDb);
		
	    $virtualDb     = $this->_config->database->virtual;
        $virtualMaster = $virtualDb->master->toArray();
        $virtualSlave  = !empty($virtualDb->slave) ? $virtualDb->slave->toArray() : null;
        $virtualDb     = new TZ_Db($virtualMaster, $virtualSlave, $virtualDb->driver);
        Yaf_Registry::set('virtual_db', $virtualDb);
        
  		$deviceDb     = $this->_config->database->device;
        $deviceMaster = $deviceDb->master->toArray();
        $deviceSlave  = !empty($deviceDb->slave) ? $deviceDb->slave->toArray() : null;
        $deviceDb     = new TZ_Db($deviceMaster, $deviceSlave, $deviceDb->driver);
        Yaf_Registry::set('device_db', $deviceDb);
	}
}

/** 
 * RouterPlugin.php
 */
class RouterPlugin extends Yaf_Plugin_Abstract 
{
    
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) 
	{
		Yaf_Dispatcher::getInstance()->disableView();
    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}
  
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}
}

/**
 * browser debug
 * 
 * @param mixed $params
 * @return void
 */
function d($params)
{
	echo '<pre>';
	var_dump($params);
	echo '</pre>';
}


