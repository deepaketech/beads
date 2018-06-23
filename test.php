<?php
 error_reporting(E_ALL);
 require_once 'app/Mage.php';
 umask(0);
 echo "<pre>";
 /* not Mage::run(); */
Mage::app();

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$color = array();
$iProductEntityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
$aOption = array();
$aOption['attribute_id'] = $installer->getAttributeId($iProductEntityTypeId, 'unit_of_');

for($iCount=0;$iCount<sizeof($color);$iCount++){
   $aOption['value']['option'.$iCount][0] = $color[$iCount];
}
$installer->addAttributeOption($aOption);

$installer->endSetup();
