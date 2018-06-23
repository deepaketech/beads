<?php

class Potato_LoginAsCustomer_Adminhtml_Potato_Loginascustomer_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function loginAction()
    {
        $customerId = $this->getRequest()->getParam('id', null);
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $storeId = $this->getRequest()->getParam('store_id', $customer->getStoreId());
        if ($customer->getId()) {
            $store = Mage::app()->getStore($storeId);

            //if customer registered from admin panel - store_id will be 0
            if (null === $store->getId() || $store->getId() == 0) {
                $website = Mage::app()->getWebsite($customer->getWebsiteId());
                $store = $website->getDefaultStore();
            }
            $this->_redirectUrl(
                $store->getUrl('po_loginascustomer/customer/login',
                    array(
                        'key' => Mage::helper('po_loginascustomer')->urlEncode(
                                Mage::helper('core')->encrypt($customer->getPasswordHash() . '|' . $customer->getId())
                            ),
                        '_secure' => $store->isCurrentlySecure()
                    )
                )
            );
            return $this;
        }
        $this->_redirectReferer();
        return $this;
    }

    protected function _isAllowed()
    {
        return (bool)Potato_LoginAsCustomer_Helper_Config::getIsAllowed();
    }
}