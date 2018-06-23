<?php
/**
 * EmJa Interactive, LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.emjainteractive.com/LICENSE.txt
 *
 * @category   EmJaInteractive
 * @package    EmJaInteractive_ShippingOption
 * @copyright  Copyright (c) 2010 EmJa Interactive, LLC. (http://www.emjainteractive.com)
 * @license    http://www.emjainteractive.com/LICENSE.txt
 */

class Emjainteractive_ShippingOption_Helper_Data extends Mage_Core_Helper_Abstract 
{
    protected $_collection;

    public function isShippingMethodAvailable()
    {
        $result = true;

        $store = null;
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
            $store = $sessionQuote->getStore();
            $customer = Mage::getModel('customer/customer')->load($sessionQuote->getCustomerId());
            $customerGroupId = $customer->getGroupId();
        }

        $carrierConfig = Mage::getStoreConfig('carriers/umosaco', $store);

        if (!isset($carrierConfig['customergroups']) || !$carrierConfig['customergroups']) {
            $result = false;
        } else {
            $allowedCustomerGroups = (strpos($carrierConfig['customergroups'], ',') !== false) ?
                explode(',', $carrierConfig['customergroups']) :
                array($carrierConfig['customergroups']);

            if (!in_array($customerGroupId, $allowedCustomerGroups)) {
                $result = false;
            }
        }

        return $result;
    }

    public function getCarrierOptions($carrier, $object = null, $updateOnChange = false)
    {
        $options = array();
        $shippingOptions = null;

        if ($object) {
            $shippingOptions = unserialize($object->getEmjainteractiveShippingoption());
        }
        if (empty($shippingOptions) && Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption()) {
            $shippingOptions = unserialize(Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption());
        }


        foreach( $this->_getOptionsCollection()->getItems() as $_option ) {
            $applyTo = explode(',', $_option->getApplyTo());
            if (in_array($carrier, $applyTo)) {
                if ($updateOnChange) {
                    $_option->setUpdateOnChange(true);
                }
                $optionHtml = $this->getLayout()->createBlock('emjainteractive_shippingoption/renderer_' . $_option->getType())
                    ->setData('option', $_option)
                    ->setData('carrier', $carrier)
                    ->setData('value', $shippingOptions)
                    ->toHtml();
                $options[$_option->getCode()] = $optionHtml; 
            }
        }
        
        return $options;
    }

    public function getOrderCarrierOptions($carrier)
    {
        $customerId = null;
        $customer = Mage::getSingleton('customer/session');
        if ($customer->isLoggedIn()) {
            $customerId = $customer->getCustomer()->getId();
        }

        $options = array();
        $shippingOptions = $this->getCustomerShippingOptions($customerId);

        if (empty($shippingOptions) && Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption()) {
            $shippingOptions = unserialize(Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption());
        }
        if (empty($shippingOptions) && Mage::getSingleton('checkout/session')->getQuote()) {
            $shippingOptions = $this->getEmjainteractiveShippingoption(Mage::getSingleton('checkout/session')->getQuote());
        }
        if (!empty($shippingOptions) && !array_key_exists($carrier, $shippingOptions)) {
            $shippingOptions = array($carrier => $shippingOptions);
        }

        foreach( $this->_getOptionsCollection()->getItems() as $_option ) {
            if (isset($shippingOptions[$carrier][$_option->getLabel()])) {
                $shippingOptions[$carrier][$_option->getCode()] = $shippingOptions[$carrier][$_option->getLabel()];
            }
        }
        
        foreach( $this->_getOptionsCollection()->getItems() as $_option ) {
            $applyTo = explode(',', $_option->getApplyTo());
            if( in_array($carrier, $applyTo) ) {
                $optionHtml = $this->getLayout()->createBlock('emjainteractive_shippingoption/renderer_' . $_option->getType())
                    ->setData('option', $_option)
                    ->setData('carrier', $carrier)
                    ->setData('value', $shippingOptions)
                    ->toHtml();

                $options[$_option->getCode()] = $optionHtml;
            }
        }

        return $options;
    }

    public function getAdminOrderCarrierOptions($carrier)
    {
        $options = array();
        $customerId = Mage::app()->getRequest()->getParam('customer_id');

        $shippingOptions = array();
        if ($reorderedId = Mage::getSingleton('adminhtml/session_quote')->getReordered()) {
            $order = Mage::getModel('sales/order')->load($reorderedId);
            if ($order->getEmjainteractiveShippingoption() != '') {
                $shippingOptions = unserialize($order->getEmjainteractiveShippingoption());
            }
        } else {
            if (Mage::getSingleton('adminhtml/session_quote')->getData('emjainteractive_shippingoption') != '') {
                $shippingOptions = unserialize(Mage::getSingleton('adminhtml/session_quote')->getData('emjainteractive_shippingoption'));
            }
            if (empty($shippingOptions)) {
                $shippingOptions = $this->getCustomerShippingOptions($customerId);
            }
        }

        foreach ($this->_getOptionsCollection()->getItems() as $_option) {
            $applyTo = explode(',', $_option->getApplyTo());
            if (in_array($carrier, $applyTo)) {
                $optionHtml = $this->getLayout()->createBlock('emjainteractive_shippingoption/renderer_' . $_option->getType())
                    ->setData('option', $_option)
                    ->setData('carrier', $carrier)
                    ->setData('value', $shippingOptions)
                    ->toHtml();

                $options[$_option->getCode()] = $optionHtml;
            }
        }

        return $options;
    }

    protected function _getOptionsCollection()
    {
        if( !$this->_collection ) {
            $this->_collection = Mage::getModel('emjainteractive_shippingoption/option')
                 ->getCollection()
                 ->applySortOrder()
                 ->load();
        }
        
        return $this->_collection;
    }
    
    public function getOrderOptionsHtml($order)
    {
        if( !$order || !$order->getId() ) {
            return false;
        }

        return $this->getLayout()->createBlock('emjainteractive_shippingoption/adminhtml_sales_order_options')
                   ->setOrder($order)
                   ->toHtml();
    }

    public function getEmjainteractiveShippingoption($object)
    {
        $_options = array();
        $options  = unserialize($object->getEmjainteractiveShippingoption());
        if (isset($options['umosaco'])) {
            $model = Mage::getSingleton('emjainteractive_shippingoption/option');
            foreach ($options['umosaco'] as $_code => $_value) {
                $model->load($_code, 'code');
                if ($model->getId()) {
                    $_options[$model->getLabel()] = $_value;
                }
            }
        }
        return $_options;
    }

    public function getCustomerShippingOptions($customerId)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'desc');

        foreach ($orderCollection as $order) {
            if ($order->getEmjainteractiveShippingoption() != '') {
                return unserialize($order->getEmjainteractiveShippingoption());
            }
        }
        
        if (Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption()) {
            return unserialize(Mage::getSingleton('checkout/session')->getEmjainteractiveShippingoption());
        }

        return null;
    }

    public function getEmailTemplateShippingOptions($options)
    {
        $model = Mage::getSingleton('emjainteractive_shippingoption/option');
        $shippingOptions = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding: 5px 8px">';
        foreach ($options as $code => $value) {
            $model->load($code, 'code');
            $shippingOptions .= '<tr>';
            $shippingOptions .= '<td width="40%" align="right" valign="top" style="font-size:12px;padding:7px 9px 9px 9px;">' . $model->getLabel() . '</td>';
            $shippingOptions .= '<td width="5%"></td>';
            $shippingOptions .= '<td align="left" valign="top" style="font-size:12px;padding:7px 9px 9px 9px;">' . $value . '</td>';
            $shippingOptions .= '</tr>';
        }
        $shippingOptions .= '</table>';

        return $shippingOptions;
    }
}
