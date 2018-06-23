<?php

class Chapagain_QuickOrder_Helper_Data extends Mage_Core_Helper_Abstract
{	
	/**
     * Retrieve configuration settings 
     * To show quickorder block in shopping cart page
     *
     * @return boolean
     */
	public function getShowInCart()
    { 		
        return Mage::getStoreConfig('catalog/chapagain_quickorder/show_in_cart');
    }    
    
    public function getVersion()
    {
		return Mage::getVersion();
	}
	
	public function getVersion19() 
	{
		if (version_compare($this->getVersion(), '1.9', '>=')){
			return true;
		} 
		return false;
	}
}
