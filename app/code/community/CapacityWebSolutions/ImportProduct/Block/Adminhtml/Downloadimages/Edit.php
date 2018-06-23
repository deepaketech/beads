<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Adminhtml_Downloadimages_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct() {
	   
	    parent::__construct();
		
        $this->_blockGroup = 'importproduct';
        $this->_controller = 'adminhtml_Importproducts';
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('back');
        $this->_removeButton('save');
    }

    public function getHeaderText()   {
	    return Mage::helper('importproduct')->__('Download Images From Live URL');
    }
}