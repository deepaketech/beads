<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Adminhtml_Exportproducts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form', 
            'action' => $this->getUrl('*/*/save'), 
            'method' => 'post', 
            'enctype' => 'multipart/form-data',
            ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }    
}