<?php 
class Mage_Catalog_Block_Product_Fulllist extends Mage_Catalog_Block_Product_Abstract {
protected function _getProductCollection()
{
    $collection = Mage::getModel('catalog/product')->getCollection()->limit(100);

    // this now has all products in a collection, you can add filters as needed.


    //    ->addAttributeToSelect('*')
    //    ->addAttributeToFilter('attribute_name', array('eq' => 'value'))
    //    ->addAttributeToFilter('another_name', array('in' => array(1,3,4)))
    //;

    // Optionally filter as above..

    return $collection;
}
}