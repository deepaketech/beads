<?php

class Potato_LoginAsCustomer_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        $key = $this->getRequest()->getParam('key');
        if (null !== $key) {
            $key = Mage::helper('core')->decrypt(Mage::helper('po_loginascustomer')->urlDecode($key));
            list($hash, $customerId) = @explode('|', $key);
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId() && $customer->getPasswordHash() == $hash) {
                $session = Mage::getSingleton('customer/session');
                if (@class_exists('Mage_Persistent_Model_Session')) {
                    //compatibility with Persistent Shopping Cart
                    Mage::dispatchEvent('persistent_session_expired');
                    $customerSession = Mage::getSingleton('customer/session');
                    $customerSession
                        ->setCustomerId(null)
                        ->setCustomerGroupId(null);
                    Mage::getSingleton('checkout/session')->unsetAll();
                    Mage::getModel('persistent/session')->removePersistentCookie();
                }
                $session
                    ->logout()
                    ->loginById($customerId)
                ;
                $dashboardUrl = $session->getAfterAuthUrl();
                if (!$dashboardUrl) {
                    $dashboardUrl = Mage::helper('customer')->getDashboardUrl();
                }
                $this->_redirectUrl($dashboardUrl);
                return $this;
            }
        }
        $this->_forward('noRoute');
    }
}