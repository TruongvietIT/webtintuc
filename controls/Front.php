<?php

class Front  extends Controller{

	public function init()
	{
		$start 		= microtime(true);
		$folderName	=  Context::getInstance()->getFolderName();
		
		$layoutPath	= 'view/layouts/'. ( $folderName ? $folderName. '/' : '' );
		
		$uri  		=  $this->getRequest()->getUrl();
		$layoutName =  Context::getInstance()->getRoute()->parseUrl($uri);
 
		
		$this->registerLayout($layoutName. 'Layout', $layoutPath);
		
		$output 	= $this->_layout->load();

		$this->getResponse()->setBody($output. '<!--Loaded Time:'. (microtime(true) - $start).' -->');
		$this->getResponse()->sendResponse();	 
		
		if ($layoutName!= 'ManageCache' && Context::getInstance()->getCacheActive()){
			$keyCache  	= Context::getInstance()->getKeyCache($this->getRequest()->getFullUrl());	
			Helper::getInstance()->getMemcachedConnection()->set($keyCache,  $output, 60);
		}
		
		$this->__destruct();
	}
}
?>
