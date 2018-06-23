<?php
 /**
 * BssCommerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BssCommerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BssCommerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    BSS_ReorderProduct
 * @author     Kenny Thang
 * @copyright  Copyright (c) 2014-2105 BssCommerce Co. (http://bsscommerce.com)
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
class Bss_ReorderProduct_Block_List extends Mage_Catalog_Block_Product_List
{
	const TYPE_CONFIGURABLE = 'configurable';
	const TYPE_SIMPLE = 'simple';
	const SORT_BY_NAME = 'name';
	const SORT_BY_RECENT_ORDER = 'recent';
	const SORT_BY_PRICE = 'price';
	const TYPE_NAME = 'Name';
	const TYPE_RECENT_ORDER = 'Recent Order';
	const TYPE_PRICE = 'Price';
	public $_TYPE_NAME = 'name';
	public $_TYPE_RECENT_ORDER = 'recent';
	public $_TYPE_PRICE = 'price';
    /**
     * Init Toolbar
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }
	
	/* Get all product
	*  return array
	*/
	public function getAllProductOrder() {
		$_array = array();
		$_data = array();
		$data = array();
		$_data1 = array();
		$data1 = array();
		/* Get all order of custom*/
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
		/* Get the customer data */
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		/* Get the customer's email address */
		$customer_email = $customer->getEmail();
		$customer_id = $customer->getId();
		$store_id = Mage::app()->getStore()->getId();
		}
		/* Get value default in admin*/
		$total = Mage::getStoreConfig('bssreorderproduct/productpage/amount');
		/* Load Order by customer and store view
		* return collection
		*/
		$collection = Mage::getModel('sales/order')
							->getCollection()
							  ->addAttributeToFilter('customer_email',array('like'=>$customer_email))
							  ->addAttributeToFilter('customer_id',array('like'=>$customer_id))
							  ->addAttributeToFilter('store_id',array('eq'=>$store_id));
		/* Check if product have total > value defaul, no show produtc in frontend*/
		foreach($collection as $col){
			//do something
			$order_id = $col->getId();
			$_productId;
			$dem = 0;
			$dem1 = 0;
			$order = Mage::getModel("sales/order")->load($order_id); //load order by order id 
			if($order->getStatusLabel() != 'Canceled'){
				$ordered_items = $order->getAllItems(); // Get all item of order
				$amount = $order->getBaseGrandTotal();  // Get Amount or item order
				if($amount > $total){ //Check to show product	
					foreach($ordered_items as $val){     //item detail
						$productId = $val ->product_id;
						if($_productId != $productId){ 
							$orders = array();
							$collections = Mage::getResourceModel('sales/order_item_collection')
								->addAttributeToFilter('product_id', array('eq' => $productId))
								->load();	
							foreach($collections as $coll){
								$orders = $coll->getOrder();
								if($orders->getStatusLabel() != 'Canceled'){
									$dem = $dem +1;
									if($orders->getBaseGrandTotal() != $amount){
										if($orders->getBaseGrandTotal() > $total){
											$dem1 = $dem1 +1;
										}
									}
								}
							}
						}
						if($dem == $dem1+1){
							$_productId = $productId;
						}
					}
				}
				/* Get product */
				
				foreach($ordered_items as $key =>$item){     //item detail   
					
					if($item->product_id != $_productId){ /* Check to show product */
						$opts = $item->getProductOptions();/* Get Options Product */
						$options = array();
						$option = array();
						$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item ->product_id);
						
						if(!$parentIds){
							$_product = Mage::getModel('catalog/product')->load($item->product_id);
							$array = array('sku'=>$item->getSku(), 'name'=>$item->getName(), 'productId'=> $item->product_id, 'qty' =>$item->getQtyOrdered(), 'price'=> $_product->getPrice(),'priceHtml'=> $this->getPriceHtml($_product,true), 'options'=>$opts['info_buyRequest'], 'option'=>$opts['options'],'bundle_options'=>$opts['bundle_options'], 'recent'=>strtotime($order->getCreatedAt()), 'attributes_info'=>$opts['attributes_info'],'last_ordered_date'=>$item->getCreatedAt(),'status' => $_product->getStatus());	

						}else{
							
							continue;
						}
						
						if(number_format($item->getPrice()) != 0){
							$_product = Mage::getModel('catalog/product')->load($item->product_id);
							$array = array('sku'=>$item->getSku(), 'name'=>$item->getName(), 'productId'=> $item->product_id, 'qty' =>$item->getQtyOrdered(), 'price'=> $_product->getPrice(),'priceHtml'=> $this->getPriceHtml($_product,true),'options'=>$opts['info_buyRequest'], 'option'=>$opts['options'],'bundle_options'=>$opts['bundle_options'], 'recent'=>strtotime($order->getCreatedAt()), 'attributes_info'=>$opts['attributes_info'],'last_ordered_date'=>$item->getCreatedAt(), 'status'=>$_product->getStatus());	
							
						}else{
							continue;
						}
							unset($opts['info_buyRequest']['qty']);
							unset($opts['info_buyRequest']['uenc']);
							unset($opts['info_buyRequest']['form_key']);
							unset($opts['info_buyRequest']['bundle_option_qty']);
							$data = array('name'=>$item->getName(),'productId'=> $item->product_id, 'options'=>$opts['info_buyRequest'] );
						if(in_array($data,$_data)) {
							$key = array_search($data,$_data);
							$_array[$key]['qty'] = $_array[$key]['qty'] + $item->getQtyOrdered();
							$_array[$key]['last_ordered_date'] = $item->getCreatedAt();
							continue;
						}else {
							$_data[] = $data;
						}
						
						$_array[] = $array; 
					}
				}
			}
		}
		/* Sort By price or recent order or name*/
		if($this->getRequest()->getParam('order')==Bss_ReorderProduct_Model_Config_Option::TYPE_PRODUCT_PRICE||Mage::getStoreConfig('bssreorderproduct/productpage/sortby') == Bss_ReorderProduct_Model_Config_Option::TYPE_PRODUCT_PRICE){
			usort($_array, function($a, $b) {
				return $a['price'] - $b['price'];		
			});
		}elseif(Mage::getStoreConfig('bssreorderproduct/productpage/sortby') == Bss_ReorderProduct_Model_Config_Option::TYPE_PRODUCT_RECENT){
			usort($_array, function($a, $b) {
				return $a['recent'] - $b['recent'];		
			});
			$_array = array_reverse($_array);
		}
		else {
			usort($_array, function($a, $b) {
				return strcmp($a['name'],$b['name']);		
			});	
		}
		return $_array;	
	}
	

	public function getOptionSortBy() {
		return array(
                array('value'  => self::SORT_BY_NAME,'label'=> self::TYPE_NAME),
				array('value' => self::SORT_BY_PRICE,'label'=> self::TYPE_PRICE),
				array('value' => self::SORT_BY_RECENT_ORDER,'label'=> self::TYPE_RECENT_ORDER),
            );
	}
	public function getOptionShow() {
		return array(
              			array('value' => 30,'label'=> 30),
				array('value' => 60,'label'=> 60),
				array('value' => 100,'label'=> 100),
            );
	}
}
