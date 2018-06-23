<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Adminhtml_Downloadimages_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	
    public function __construct()  {
        parent::__construct();

        $this->setId('import_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('importproduct')->__('Import/Export Products'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('sample_csv', array(
		'label'   => Mage::helper('importproduct')->__('How To Use'),
            'title'   => Mage::helper('importproduct')->__('How To Use'),
            'content' => $this->getLayout()->createBlock('importproduct/adminhtml_downloadimages_edit_tab_samplecsv')->toHtml(), 
            ));
		$this->addTab('upload_file', array(
		'label'   => Mage::helper('importproduct')->__('Download Products Images'),
            'title'   => Mage::helper('importproduct')->__('Import Products Images'),
            'content' => $this->getLayout()->createBlock('importproduct/adminhtml_downloadimages_edit_tab_uploadfile')->toHtml(), 
            ));
		 
	
        return parent::_beforeToHtml();
    }    
}