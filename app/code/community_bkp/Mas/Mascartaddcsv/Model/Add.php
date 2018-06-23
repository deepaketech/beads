<?php
/**
 * Mas_Mascartaddcsv extension by Makarovsoft.com
 * 
 * @category   	Mas
 * @package		Mas_Mascartaddcsv
 * @copyright  	Copyright (c) 2014
 * @license		http://makarovsoft.com/license.txt
 * @author		makarovsoft.com
 */
/**
 * Test model
 *
 * @category	Mas
 * @package		Mas_Mascartaddcsv
 * 
 */
class Mas_Mascartaddcsv_Model_Add extends Mage_Core_Model_Abstract {
	
	public function _construct(){
		parent::_construct();	
	}	
	
	public function add($data) 
	{
        $skus = array_keys($data);
        
        $_products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents() 
	        ->addAttributeToSelect(array('name', 'product_url', 'small_image', 'sku'))
	        ->addAttributeToFilter('sku', array('in' => $skus));
	        
		Mage::getSingleton('cataloginventory/stock')->addItemsToProducts($_products);
        
        
        $cart = Mage::getSingleton('checkout/cart');
        $cart->init();
        
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        	$url = Mage::getUrl('mascartaddcsv/index');
		} else {
			$url = Mage::app()->getRequest()->getServer('HTTP_REFERER');
		}
        
        $added = 0;
        $qty = 0;
        
        $p = array();
        
        try {
	        foreach ($_products as $_product) {
	            $cart->addProduct($_product, array('qty' => $data[$_product->getSku()][1]));
	            $added++;
	            $qty += $data[$_product->getSku()][1];
	            
	            if (isset($data[$_product->getSku()][2])) {
	            	$p[$_product->getId()] = $data[$_product->getSku()][2];
	            }
	        }
        } catch (Exception $ex) {
			Mage::getSingleton('core/session')->addError(Mage::helper('mascartaddcsv')->__('%s can not be added to cart. Error is: %s <br /><a href="%s">Click here</a> to correct your import file', $_product->getName(), $ex->getMessage(), $url));
        }
        $cart->save();
        
		if ('true' == (string)Mage::getConfig()->getNode('modules/Mas_Masoic/active')) {
        	$items = $cart->getItems();
        	$comments = array();
        	foreach($items as $ci) {
        		$comments[$ci->getItemId()] = array(
        			'comment' => $p[$ci->getProductId()]
        		);
        	}
        	Mage::getResourceModel('masoic/comment')->updateQuote($comments, Mage::getSingleton('checkout/session')->getQuoteId());
		}
        
        if ($added > 0) {
        	Mage::getSingleton('core/session')->addSuccess(Mage::helper('mascartaddcsv')->__('%d unique products have been added to cart. %d items have been added in total. <br /><a href="%s">Add new products?</a>', $added, $qty, $url));
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	}
}