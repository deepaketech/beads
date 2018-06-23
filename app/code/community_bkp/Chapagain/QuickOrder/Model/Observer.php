<?php
 
class Chapagain_QuickOrder_Model_Observer
{
    public function insertBlock($observer)
    {
        /** @var $_block Mage_Core_Block_Abstract */
        /*Get block instance*/
        $_block = $observer->getBlock();
        
        /*get Block type*/
        $_type = $_block->getType();
        
       /*Check block type*/
        if ($_type == 'checkout/cart_coupon') { 
            /*Clone block instance*/
            $_child = clone $_block;
            /*set another type for block*/
            $_child->setType('quickorder/quickorder');
            /*set child for block*/
            $_block->setChild('child.coupon', $_child);
            /*set our template*/
            $_block->setTemplate('chapagain_quickorder/block_cart.phtml');
        }
        
    }
}
