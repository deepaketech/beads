<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Adminhtml_Importproducts_Edit_Tab_Uploadfile extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('importproduct/upload.phtml');
    }

    public function getPostMaxSize()
    {
        return ini_get('post_max_size');
    }
	
	public function getMaxExecutionTime()
    {
        return ini_get('max_execution_time');
    }

    public function getUploadMaxSize()
    {
        return ini_get('upload_max_filesize');
    }

    public function getDataMaxSize()
    {
        return min($this->getPostMaxSize(), $this->getUploadMaxSize());
    }
}