<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

error_reporting(0);
class  CapacityWebSolutions_ImportProduct_Adminhtml_LicenseManagerController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
	{
	$this->loadLayout()
			->_setActiveMenu('cws')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;		
	} 
	public function indexAction() {			
		$this->_initAction()->renderLayout();
	}
}
?>