<?php

class Potato_LoginAsCustomer_Model_Observer
{
    public function addLoginAsCustomerButton(Varien_Object $observer)
    {
        $containerBlock = $observer->getBlock();
        if (!($containerBlock instanceof Mage_Adminhtml_Block_Customer_Edit ||
                $containerBlock instanceof Mage_Adminhtml_Block_Sales_Order_View) ||
            !$this->_getCustomerId()
            || !Potato_LoginAsCustomer_Helper_Config::getIsAllowed()
        ) {
            return $this;
        }
        $params = array(
            'id' => $this->_getCustomerId()
        );
        if (Mage::registry('current_order')) {
            $params['store_id'] = Mage::registry('current_order')->getStoreId();
        }
        $containerBlock->addButton('login',
            array(
                'label' => Mage::helper('po_loginascustomer')->__('Login As Customer'),
                'onclick' => 'window.open(\'' . Mage::getModel('adminhtml/url')->getUrl(
                        'adminhtml/potato_loginascustomer_customer/login',
                        $params
                    ) . '\', \'_blank\');'
                ,
            ),
            0
        );
        return $this;
    }

    protected function _getCustomerId()
    {
        $customerId = null;
        if (Mage::registry('current_customer')) {
            $customerId = Mage::registry('current_customer')->getId();
        }
        if (Mage::registry('sales_order')) {
            $customerId = Mage::registry('sales_order')->getCustomerId();
        }
        return $customerId;
    }
}