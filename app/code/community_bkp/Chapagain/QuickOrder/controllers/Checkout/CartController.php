<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Chapagain_QuickOrder_Checkout_CartController extends Mage_Checkout_CartController
{
	/**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
	protected function _initProduct()
	{
		$sku = $this->getRequest()->getParam('sku');
		$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
		
		if ($productId) {
			$product = Mage::getModel('catalog/product')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($productId);
						
			if ($product->getId() 
				&& $product->isSaleable() 
				&& $this->isEnabled($product->getStatus()) 
				&& $product->isVisibleInSiteVisibility()
				) {
				return $product;
			}
		}
		return false;
	}

	public function isEnabled($status)
	{
		return $status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
	}
	
	/**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {        
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            
            /**
             * Check product availability
             */
            if (!$product) {
				$this->_getSession()->addError($this->__('Cannot add the item to shopping cart.') . $this->__(' ( SKU: '.$params['sku'].' )'));
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);            
            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
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
