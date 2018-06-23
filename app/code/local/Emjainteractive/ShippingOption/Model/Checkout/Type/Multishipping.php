<?php

class Emjainteractive_ShippingOption_Model_Checkout_Type_Multishipping extends Mage_Checkout_Model_Type_Multishipping
{
    public function setShippingMethods($methods)
    {
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            if (isset($methods[$address->getId()])) {
                $address->setShippingMethod($methods[$address->getId()]);
            } elseif (!$address->getShippingMethod()) {
                Mage::throwException(Mage::helper('checkout')->__('Please select shipping methods for all addresses'));
            }
        }
        $this->save();
        return $this;
    }
}
