<?php
require_once 'app/Mage.php';
Mage::app();

$client = new SoapClient('http://37.60.231.179/~beadmast/beads/api/soap/?wsdl');
ini_set("soap.wsdl_cache_enabled", "0");
$session = $client->login('anas', 'well@123');
$productId = 20058;
$result = $client->call($session, 'catalog_product.info', $productId);


$pro1 = $client->newInfo();
var_dump($pro1);

/*$new_price = array(array ('cust_group'=>1,'price'=>10.22),array('cust_group'=>3, 'price'=>10.36));

//$resource = Mage::getSingleton('core/resource');
//$writeConnection = $resource->getConnection('core_write');

//foreach($new_price as $newprice){
  //  $query = "UPDATE catalog_product_entity_group_price SET value =".$newprice['price']." WHERE entity_id=".$productId." AND    //customer_group_id=".$newprice['cust_group'];
//$writeConnection->query($query);
//print $query;
}*/
