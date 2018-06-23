<?php
 /**
 * BssCommerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BssCommerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BssCommerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    BSS_ReorderProduct
 * @author     Kenny Thang
 * @copyright  Copyright (c) 2014-2105 BssCommerce Co. (http://bsscommerce.com)
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
class Bss_ReorderProduct_ListController extends Mage_Core_Controller_Front_Action
{
	public $_status_finish = 1;
	public $_status_going = 2;
	public $_status_waiting = 3;
	/* Check account is loginned */
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }
	
    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Reorder Product'));
        $this->renderLayout();
    }
	
	 protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
	/* Get Product
	* @param $productId
	* return product
	*/
	protected function _initProduct($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
	
	/* Function add to cart item*/
	public function addAction () {
		$cart = Mage::getModel('checkout/cart');
		try {
			if($this->getRequest()->getPost()){
				/* Get value */
				$param = $this->getRequest()->getParams();
				$productId = $param['productId'];
				$qty = $param['qty'];
				$options = unserialize($param['options']);
				if(isset($qty)) {
					$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$options['qty'] = $filter->filter($qty);
				}
				$product = $this->_initProduct($productId);
				/**
				 * Check product availability
				 */
				if (!$product) {
					$this->_goBack();
					return;
				}
				/* Add product and param into cart */
				$cart->addProduct($product, $options);
				if (!empty($options['related_product'])) {
					$cart->addProductsByIds(explode(',', $options['related_product']));
				}
				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);
				 /**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);
			}	
		} catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
					}
				}

				$url = $this->_getSession()->getRedirectUrl(true);
				if ($url) {
					$this->getResponse()->setRedirect($url);
				} else {
					$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
				}
			} catch (Exception $e) {
				$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
				Mage::logException($e);
				$this->_goBack();
			}
		
        
	}
	/* Function add to cart item*/
	public function addtocartAction () {
		$data = array();
		$cart = Mage::getModel('checkout/cart');
		try {
			/* Check data */
			if($this->getRequest()->getPost()){
				/* Get Data*/
				$param = $this->getRequest()->getParams();
				$str = $param['str'];
				$array = explode("@",$str);
				for ($i=0; $i< count($array); $i++){
					$_str = explode("!",$array[$i]);
					$qty = $_str[0];
					$productId = $_str[1];
					$options = unserialize(str_replace('$','"',$_str[2]));
					
					if(isset($qty)) {
						$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
						);
						$options['qty'] = $filter->filter($qty);;
					}
					$product = $this->_initProduct($productId);	
					/**
					 * Check product availability
					 */
					if (!$product) {
						$this->_goBack();
						return;
					}
					/* Add product into Cart*/					
					$cart->addProduct($product, $options);
					if (!empty($options['related_product'])) {
						$cart->addProductsByIds(explode(',', $options['related_product']));
					}					
				}
				/* Save product Into Cart*/
				$cart->save();
				
				$this->_getSession()->setCartWasUpdated(true);
				/**
				* @todo remove wishlist observer processAddToCart
				*/
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);
			}	
		} catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
					}
				}

				$url = $this->_getSession()->getRedirectUrl(true);
				if ($url) {
					$this->getResponse()->setRedirect($url);
				} else {
					$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
				}
		} catch (Exception $e) {
				$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
				Mage::logException($e);
				$this->_goBack();
		}
	}
	
}
