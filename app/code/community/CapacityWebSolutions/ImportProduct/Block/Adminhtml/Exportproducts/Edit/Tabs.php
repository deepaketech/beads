<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Adminhtml_Exportproducts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('import_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('importproduct')->__('Import/Export Products'));
    }

    protected function _beforeToHtml()
    {	
        $this->addTab('run_profile', array(
            'label'   => Mage::helper('importproduct')->__('Export All Products'),
            'title'   => Mage::helper('importproduct')->__('Export All Products'),
            'content' => $this->getLayout()->createBlock('importproduct/adminhtml_exportproducts_edit_tab_runprofile')->toHtml(), 
            ));	
			
        $this->addTab('exported_files', array(
            'label'   => Mage::helper('importproduct')->__('Exported Files'),
            'title'   => Mage::helper('importproduct')->__('Exported Files'),
            'content' => $this->getLayout()->createBlock('importproduct/adminhtml_exportproducts_edit_tab_exportedfile')->toHtml(), 
            ));				
			
        return parent::_beforeToHtml();
    }	
}