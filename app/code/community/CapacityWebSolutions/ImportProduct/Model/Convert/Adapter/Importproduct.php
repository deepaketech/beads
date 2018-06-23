<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Model_Convert_Adapter_Importproduct
extends Mage_Eav_Model_Convert_Parser_Abstract
{

    const MULTI_DELIMITER   = ' , ';
    const ENTITY            = 'catalog_product_import';

	protected $_error=array();
    protected $_productModel;
	protected $_productTypes;
	protected $custom_options = array();
    protected $custom_flag;
    protected $_productTypeInstances = array();
    protected $_productAttributeSets;
    protected $_stores;
    protected $_attributes = array();
    protected $_configs = array();
    protected $_requiredFields = array();
    protected $_ignoreFields = array();
    protected $_imageFields = array();
    protected $_inventoryFields             = array();
	public $skudata=array();
    protected $_inventoryFieldsProductTypes = array();
    protected $_toNumber = array();
	protected $_categoryCache = array();

	public function runImport(array $data)
	{
		$this->_error=array();
		$this->saveRow(unserialize($data['product_data']));		
		foreach($this->_error as $e)
		{
			$model=Mage::getModel('importproduct/importlog');
			$model->setErrorInformation($e['txt']);
			$model->setErrorType($e['error_level']);
			$model->setProductSku($e['product_sku']);
			$model->save();
		}		
		return $this->_error;
	}
	
	public function parse(){
	}
    
	public function unparse(){
	}
	
    protected function _addCategories($categories, $store)
    {
		$rootId = $store->getRootCategoryId();
		$PARENT_SEPARATOR = Mage::getStoreConfig('importproduct/importproductseparators_group/separators_parentcategories');
		$CHILD_SEPARATOR = Mage::getStoreConfig('importproduct/importproductseparators_group/separators_childcategories');
        if (!$rootId) {
			$storeId = 1;
		 	$rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
        }
		
		if($categories=="")
		   return array();
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$store->getId()])) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");

            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
                    $name = $collection->getItemById($pathArr[$i])->getName();
					$namePath .= (empty($namePath) ? '' : $CHILD_SEPARATOR).trim($name);
                }
                $cat->setNamePath($namePath);
            }
            $cache = array();
            foreach ($collection as $cat) {
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];
        $catIds = array();
			foreach (explode($PARENT_SEPARATOR, $categories) as $categoryPathStr) {
				$categoryPathStr = preg_replace('#\s*/\s*#', $CHILD_SEPARATOR, trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
				foreach (explode($CHILD_SEPARATOR, $categoryPathStr) as $catName) {
					$namePath .= (empty($namePath) ? '' : $CHILD_SEPARATOR).strtolower($catName);
                if (empty($cache[$namePath])) {
                    $cat = Mage::getModel('catalog/category')
                        ->setStoreId($store->getId())
                        ->setPath($path)
                        ->setName($catName)
                        ->setIsActive(1)
                        ->save();
                    $cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/'.$catId;
            }
            if ($catId) {
                $catIds[] = $catId;
            }
        }
        return join(',', $catIds);
    }
 
    public function getProductModel()
    {
        if (is_null($this->_productModel)) {	
			$productModel = Mage::getModel('catalog/product');
			$this->_productModel = Mage::objects()->save($productModel);		
		}
        return Mage::objects()->load($this->_productModel);
    }

    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getProductModel()->getResource()->getAttribute($code);
        }
        if ($this->_attributes[$code] instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $applyTo = $this->_attributes[$code]->getApplyTo();
            if ($applyTo && !in_array($this->getProductModel()->getTypeId(), $applyTo)) {
                return false;
            }
        }
        return $this->_attributes[$code];
    }

    
    public function getProductTypes()
    {  
		return Mage::registry('product_type');
    }

    
    public function setProductTypeInstance(Mage_Catalog_Model_Product $product)
    {
        $type = $product->getTypeId();
        if (!isset($this->_productTypeInstances[$type])) {
            $this->_productTypeInstances[$type] = Mage::getSingleton('catalog/product_type')
                ->factory($product, true);
        }
        $product->setTypeInstance($this->_productTypeInstances[$type], true);
        return $this;
    }

    
    public function getProductAttributeSets()
    {
		return Mage::registry('product_attribute_set');
    }
  
    protected function _initStores()
    {
 		 $this->_stores=Mage::registry('store_code');		
		 $this->_storesIdCode=Mage::registry('store');		
    }

    public function getStoreByCode($store)
    {
        $this->_initStores();
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    public function getStoreById($id)
    {
        $this->_initStores();
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }
        if (isset($this->_storesIdCode[$id])) {
            return $this->getStoreByCode($this->_storesIdCode[$id]);
        }
        return false;
    }
	
    public function __construct()
    {
		$load_basic_param=Mage::registry('load_basic_param');
		$this->_inventoryFieldsProductTypes=$load_basic_param['_inventoryFieldsProductTypes'];
		$this->_inventoryFields=$load_basic_param['_inventoryFields'];
		$this->_requiredFields=$load_basic_param['_requiredFields'];
		$this->_ignoreFields=$load_basic_param['_ignoreFields'];
		$this->_toNumber=$load_basic_param['_toNumber'];
		
        $this->setVar('entity_type', 'catalog/product');
        if (!Mage::registry('Object_Cache_Product')) {
            $this->setProduct(Mage::getModel('catalog/product'));
        }

        if (!Mage::registry('Object_Cache_StockItem')) {
            $this->setStockItem(Mage::getModel('cataloginventory/stock_item'));
        }
    }


    public function getNumber($value)
    {
        $allow  = array('0',1,2,3,4,5,6,7,8,9,'-','.');
        $number = '';
        for ($i = 0; $i < strlen($value); $i ++) {
            if (in_array($value[$i], $allow)) {
                $number .= $value[$i];
            }
        }
        if ($separator != '.') {
            $number = str_replace($separator, '.', $number);
        }
        return floatval($number);
    }
	
    public function setProduct(Mage_Catalog_Model_Product $object)
    {
        $id = Mage::objects()->save($object);
        Mage::register('Object_Cache_Product', $id);
    }

    public function getProduct()
    {
        return Mage::objects()->load(Mage::registry('Object_Cache_Product'));
    }

    public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $object)
    {
        $id = Mage::objects()->save($object);
        Mage::register('Object_Cache_StockItem', $id);
    }

    public function getStockItem()
    {
        return Mage::objects()->load(Mage::registry('Object_Cache_StockItem'));
    }
	
    public function saveRow(array $importData)
    {
		$product_type='';
		$import_custom_option=true;
		
		/* Code for trim the values  */
		foreach($importData as $key => $value):
			$importData[$key] = trim($value);
		endforeach;
		/* End of Code for trim the values  */
		
		/* This code is Added for Manage without slash name image names */
		if($importData["image"] != "" && substr_count($importData["image"],"/") == 0){
			$importData["image"] = "/".$importData["image"];
		}
		if($importData["image"] != "" && substr_count($importData["small_image"],"/") == 0){
			$importData["small_image"] = "/".$importData["small_image"];
		}
		if($importData["image"] != "" && substr_count($importData["thumbnail"],"/") == 0){
			$importData["thumbnail"] = "/".$importData["thumbnail"];
		}
		if($importData["gallery"] != ""){
			$gallery_image = array();
			$gallery_image = explode("|",$importData["gallery"]);
			foreach($gallery_image as $value){
				if(substr_count(trim($value),"/") == 0){
					$importData["gallery1"] .= "/".$value."|";
				}else{
					$importData["gallery1"] .= $value."|";
				}
			}
			$importData["gallery"] = $importData["gallery1"];
		}
		/* End of code for Manage without slash name image names */
		
		
		$subject = var_export($importData, true);
		$custome_option_name = array();
		preg_match_all('/cws!(.*?):(.*?):\\d/', $subject, $result, PREG_PATTERN_ORDER);
		for ($i = 0; $i < count($result[0]); $i++) {
			$custome_option_name[$i] = $result[0][$i];
		}
		
	    $this->_productModel=null;
        $product = $this->getProductModel()->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined. Product SKU# '.$importData['sku'], 'store');
				Mage::log($message,null,'cws_import_product_log.txt');
				array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));	
				return ;
            }
        }
        else {
             $store = $this->getStoreByCode($importData['store']);
        }
 
        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists. Product SKU# '.$importData['sku'], $importData['store']);
			Mage::log($message,null,'cws_import_product_log.txt');
			array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));	
        }
 
        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined. Product Name# '.$importData['name'], 'sku');
			Mage::log($message,null,'cws_import_product_log.txt');
			array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));	
        }
        $productId = $product->getIdBySku($importData['sku']);
		$behavior=Mage::app()->getRequest()->getParam('behavior');
 
        if ($productId) {
			$product->load($productId);		
		
			if($behavior=='delete')
			{
				/* This code is used for Delete product image from folders */
				$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
				$items = $mediaApi->items($product->getId());
				foreach($items as $item) {
					$mediaApi->remove($product->getId(), $item['file']);
					unlink(Mage::getBaseDir('media').DS.'catalog'.DS.'product'.$item['file']);
				}
				/* End of Delete product image from folders code */
				$product->delete();
				
				return ;
			}
			//Code Added By Parag For Fix the update issue
			$productTypes = $this->getProductTypes();
			$product->setTypeId($productTypes[strtolower($importData['type'])]);
			$product_type=$productTypes[strtolower($importData['type'])];
			$product->load($productId);		
			$product->setStoreId($store->getId());
        }else {
			if($behavior=='delete')
			{
				$message ="Product does not exists, can't delete it.";
				array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));
				return true;
			}
			$product->setStoreId(0);
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();
			
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $messagemessagemessage = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s" . Product SKU# '.$importData['sku'], $value, 'type');
				Mage::log($message,null,'cws_import_product_log.txt');
				array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));	
				
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
			$product_type=$productTypes[strtolower($importData['type'])];
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);
 
            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined. Product SKU# '.$importData['sku'], $field);
					Mage::log($message,null,'cws_import_product_log.txt');					
					array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));
                }
            }
        }
 
        $this->setProductTypeInstance($product);
		
        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }
		if (isset($importData['categories'])) {
			$categoryIds = $this->_addCategories($importData['categories'], $store);
            if ($categoryIds) {
                $product->setCategoryIds($categoryIds);
            }
        }
        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }
 
        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }
 
        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }
 		$is_required_status=false;
        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (in_array($field, $this->_imageFields)) {
                continue;
            }
            $attribute = $this->getAttribute($field);
          	if (!$attribute) {
			
				if(strpos($field,':')!==FALSE && strlen($value)) {
				   $values=explode('|',$value);
				   if(count($values)>0) {
					  @list($title,$type,$is_required,$sort_order) = explode(':',$field);
					  $title = ucfirst(str_replace('_',' ',$title));
					  @list($other,$title) = explode('!',$title);
					  $custom_flag=$other;
					  $custom_options[] = array(
						 'is_delete'=>0,
						 'title'=>$title,
						 'previous_group'=>'',
						 'previous_type'=>'',
						 'type'=>$type,
						 'is_require'=>$is_required,
						 'sort_order'=>$sort_order,
						 'values'=>array()
					  );

					  if($is_required==true || $is_required=='1')
					  {
						$is_required_status=true;
					  }					  
					  
					  foreach($values as $v) {
						 $parts = explode(':',$v);
						 $title = $parts[0];
						 if(count($parts)>1) {
							$price_type = $parts[1];
						 } else {
							$price_type = 'fixed';
						 }
						 if(count($parts)>2) {
							$price = $parts[2];
						 } else {
							$price =0;
						 }
						 if(count($parts)>3) {
							$sku = $parts[3];
						 } else {
							$sku='';
						 }
						 if(count($parts)>4) {
							$sort_order = $parts[4];
						 } else {
							$sort_order = 0;
						 }
						 
						 if(count($parts)>5) {
							$price_type=$parts[0];
							$price = $parts[1];
							$sku =$parts[2];
							$fileextension = $parts[3];
							$imagesizex =$parts[4];
							$imagesizey= $parts[5];
						}
						 
						 switch($type) {
							case 'file':
								$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
								$custom_options[count($custom_options) - 1]['price'] = $price;
								$custom_options[count($custom_options) - 1]['sku'] = $sku;
								$custom_options[count($custom_options) - 1]['file_extension'] = $fileextension;
								$custom_options[count($custom_options) - 1]['image_size_x'] = $imagesizex;
								$custom_options[count($custom_options) - 1]['image_size_y'] = $imagesizey;
							     break;
							   
							case 'field':
								$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
								$custom_options[count($custom_options) - 1]['price'] = $price;
								$custom_options[count($custom_options) - 1]['sku'] = $sku;
								$custom_options[count($custom_options) - 1]['max_characters'] = $sort_order;
							case 'area':
							   $custom_options[count($custom_options) - 1]['max_characters'] = $sort_order;
							case 'date':
							case 'date_time':
							case 'time':
							   $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							   $custom_options[count($custom_options) - 1]['price'] = $price;
							   $custom_options[count($custom_options) - 1]['sku'] = $sku;
							   break;
														  
							case 'drop_down':
							case 'radio':
							case 'checkbox':
							case 'multiple':
							default:
							   $custom_options[count($custom_options) - 1]['values'][]=array(
								  'is_delete'=>0,
								  'title'=>$title,
								  'option_type_id'=>-1,
								  'price_type'=>$price_type,
								  'price'=>$price,
								  'sku'=>$sku,
								  'sort_order'=>$sort_order,
							   );
							   break;
						 }
					  }
				   }
				}
                continue;
            }
            $isArray = false;
            $setValue = $value;
 
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }
 
            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }
 			
            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);
 
                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
						/* This Code is added by Simoni for fixed custom_design field issue. */
						if($field=="custom_design"){
							$setValue=$value;
						}
						/* End of added code by Simoni */
                    }
                }
            }
            $product->setData($field, $setValue);
        }
 
        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }
 
        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                }else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);
		 
		if(isset($importData['small_image']) && isset($importData['image']) && isset($importData['thumbnail']) && isset($importData['gallery'])){
	        $attributes = $product->getTypeInstance()->getSetAttributes();	        
        	if (isset($attributes['media_gallery'])) {
				$gallery = $attributes['media_gallery'];
				$galleryData = $product->getMediaGallery();								
				foreach($galleryData['images'] as $image){
			    	if ($gallery->getBackend()->getImage($product, $image['file'])) {
			            $gallery->getBackend()->removeImage($product, $image['file']);
						unlink(Mage::getBaseDir('media').DS.'catalog'.DS.'product'.$image['file']);
			        }
				}
			}
        }
        $imageData = array();
        foreach ($this->_imageFields as $field) {
            if (!empty($importData[$field]) && $importData[$field] != 'no_selection') {
                if (!isset($imageData[$importData[$field]])) {
                    $imageData[$importData[$field]] = array();
                }
                $imageData[$importData[$field]][] = $field;
            }
        }
				
		if(isset($importData['small_image']) && isset($importData['image']) && isset($importData['thumbnail']) && isset($importData['gallery'])){
 		if(Mage::getVersion() < "1.5.0.0"){
			foreach ($imageData as $file => $fields) {
				try {
					$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $fields);
				}
				catch (Exception $e) {
					Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
					array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));	
					return true;					
				}
			}
		}else{
			$mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();
			$arrayToMassAdd = array();
			foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
				if (isset($importData[$mediaAttributeCode])) {
					$file = $importData[$mediaAttributeCode];
					if (trim($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
						$arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
					}
				}
			}try{
				$addedFilesCorrespondence =	$mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes($product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false, false);
			}catch(Exception $e){
					Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
					array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));	
					return true;					
			}

			foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
				$addedFile = '';
				if (isset($importData[$mediaAttributeCode . '_label'])) {
					$fileLabel = trim($importData[$mediaAttributeCode . '_label']);
					if (isset($importData[$mediaAttributeCode])) {
						$keyInAddedFile = array_search($importData[$mediaAttributeCode],
							$addedFilesCorrespondence['alreadyAddedFiles']);
						if ($keyInAddedFile !== false) {
							$addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
						}
					}

					if (!$addedFile) {
						$addedFile = $product->getData($mediaAttributeCode);
					}
					if ($fileLabel && $addedFile) {
						$mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
					}
				}
			}
		}
		try 
		{
			$galleryData = explode('|',$importData["gallery"]);
			foreach($galleryData as $gallery_img)
			{
				$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, null, false, false);
				}
			}
			catch (Exception $e) {
			}        
		}

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

		if($product_type=='configurable'){
			$used_attribute=explode(",", $importData['used_attribute']);
			$attributeIds=Array();
			foreach($used_attribute as $attrCode){
				$super_attribute= Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$attrCode);
				if($super_attribute){
					$configurableAtt = Mage::getModel('catalog/product_type_configurable_attribute')->setProductAttribute($super_attribute);
					
					if($configurableAtt){
							$attributeIds[$attrCode]=$configurableAtt->getId();			
							$newAttributes[] = array(
							   'id'             => $configurableAtt->getId(),
							   'label'          => $configurableAtt->getLabel(),
							   'position'       => $super_attribute->getPosition(),
							   'values'         => $configurableAtt->getPrices() ? $configProduct->getPrices() : array(),
							   'attribute_id'   => $super_attribute->getId(),
							   'attribute_code' => $super_attribute->getAttributeCode(),
							   'frontend_label' => $super_attribute->getFrontend()->getLabel(),
								);
					}else{
					   $message='Attribute with code : '.$attrCode.' is not configurable attribute.';
					   array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
					   Mage::log('Product SKU: '.$importData['sku']." ".$message,null,'cws_import_product_log.txt');					   
					}
					
				}else{
				   $message='Attribute Code : '.$attrCode.' doest not exists. ';
				   array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
				   Mage::log('Product SKU: '.$importData['sku']." ".$message,null,'cws_import_product_log.txt');					   				   
				}
			}
			
			$configurableData = array();
			$childConfigurePro=explode(",", $importData['child_products_sku']);
			foreach($childConfigurePro as $childc){
				$childProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $childc);

				if($childProduct){
					foreach($used_attribute as $uatt){					
							$configurableData[$childProduct->getId()]=array(
								'attribute_id' => $attributeIds[$uatt]
							);				
					}
				}else{
				   $message='Product doest not exists. SKU: '.$childc;
				   array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
				   Mage::log($message,null,'cws_import_product_log.txt');
				}
			}

			if(count($newAttributes)!=0){
				try{
					$product->setConfigurableAttributesData($newAttributes);
				}catch(Exception $e){				
					array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));
					Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
					return true;
				}
			}else{
				$message='Configurable products not created. Configurable attribute does not exist.';
				array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
				Mage::log('Product SKU: '.$importData['sku']." ".$message(),null,'cws_import_product_log.txt');				  			    
				return true;
			}
			
			if(count($configurableData)!=0){	
				try{
					$product->setConfigurableProductsData($configurableData);
				}catch(Exception $e){
					array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));
					Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
					return true;					
				}
			}
		} else if($product_type=='bundle'){
			$product->setPriceType($this->getPriceType($importData['price_type']));
			$product->setPriceView($this->getPriceView($importData['price_view']));
			
			if($importData['weight']!=''){
				$product->setWeightType(1);
			}			
			
			if($this->getPriceType($importData['price_type'])=='0'){
				$import_custom_option=false;
			}
			
			Mage::register('product', $product);
			Mage::register('current_product', $product);
						
			$option_str=$importData['bundle_product_options'];
			$single_bundle_option=explode("|",$option_str);
			$optionRawData = array();
			$selectionRawData = array();

			for($z=0;$z<count($single_bundle_option);$z++){
				$single_bundle_option_data=explode(":",$single_bundle_option[$z]);			
				$single_bundle_option_title_value=explode(",",$single_bundle_option_data[0]);			
				$optionRawData[$z] = array(
					  'required' => $single_bundle_option_title_value[2],
					  'option_id' => '',
					  'position' => $single_bundle_option_title_value[3],
					  'type' => $single_bundle_option_title_value[1],
					  'title' => $single_bundle_option_title_value[0],
					  'default_title' => $single_bundle_option_title_value[0],
					  'delete' => '',
					);
				$single_bundle_option_selection_value=explode("!",$single_bundle_option_data[1]);
				foreach($single_bundle_option_selection_value as $singleBundleOptionData){	
					$d=explode(',',$singleBundleOptionData);
					$product_id = Mage::getModel("catalog/product")->getIdBySku($d[0]);
					if($product_id){
						$selectionRawData[$z][] = array(
						  'product_id' => $product_id,
						  'selection_qty' => $d[1],
						  'selection_can_change_qty' => $d[2],
						  'position' => $d[3],
						  'is_default' => $d[4],
						  'selection_id' => '',
						  'selection_price_type' => 0,
						  'selection_price_value' => 0.0,
						  'option_id' => '',
						  'delete' => ''
						);	
					}else{
						$message='Product does not exist. SKU :'.$d[0];
						array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
						Mage::log($message,null,'cws_import_product_log.txt');									
					}
				}
			}

			$product->setCanSaveConfigurableAttributes(false);
			$product->setCanSaveCustomOptions(true);
			$product->setBundleOptionsData($optionRawData);
			$product->setBundleSelectionsData($selectionRawData);
			$product->setCanSaveBundleSelections(true);
			$product->setAffectBundleProductSelections(true);

		}else if($product_type=='downloadable'){
				$main_option_array = array();
				$main_temp_array=array();
				$filearrayforimport = array();
			  	$filenameforsamplearrayforimport = array();
				$product->setLinksTitle("Download");
				$product->setLinksPurchasedSeparately($importData['link_can_purchase_separately']);
				
				try{
					$product->save();
				}catch(Exception $e){
					Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');									
					array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku']));	
				}

				$downloadable_product_main_data = explode('|',$importData['downloadable_product_options']);

				foreach ($downloadable_product_main_data as $single) {
					$single_row=explode(";",$single);
					$linkdata=$single_row[0];	
					$linkdata=explode(",",$linkdata);		
					$sampledata=$single_row[1];		
					$sampledata=explode(",",$sampledata);
						
						$basic_field=array(
								'product_id' => $product->getId(),
								'sort_order' => 0,
								'number_of_downloads' => $linkdata[2], // Unlimited downloads
								'is_shareable' => $linkdata[3], // Not shareable
								'link_type' => $linkdata[4],
								'sample_type' => $sampledata[0],
								'use_default_title' => false,
								'title' => $linkdata[0],
								'default_price' => 0,
								'price' => $linkdata[1],
								'store_id' => 0,
								'website_id' => $product->getStore()->getWebsiteId(),
						);

								$linkimagename=ltrim($linkdata[5], '/');	
								$sampleimagename=ltrim($sampledata[1], '/');
								$linkfile = array();
								$samplefile = array();
								$_highfilePath =$linkimagename;
								$_samplefilePath=$sampleimagename;
								
								$samplefile[] = array(
										'file' => $_samplefilePath,
										'name' => $sampleimagename,
										'status' => 'new'
								);

								$linkfile[] = array(
										'file' => $_highfilePath,
										'name' => $linkimagename,                
										'status' => 'new'
								);
								
								if($linkdata[4]=='file'){								
									$basic_field['link_file']=json_encode($linkfile);
									$basic_field['link_url']='';									
								}else{
									$basic_field['link_file']='';
									$basic_field['link_url']=$linkimagename;
								}
								
								if($sampledata[0]=='file'){								
									$basic_field['sample_file']=json_encode($samplefile);
									$basic_field['sample_url']='';									
								}else{
									$basic_field['sample_file']='';
									$basic_field['sample_url']=$sampleimagename;
								}
								
								$linkModel = Mage::getModel('downloadable/link')->setData($basic_field);
								
								if($linkdata[4]=='file' || $linkdata[4]==''){
								
									if (!file_exists(Mage::getBaseDir('media').DS. 'import'.DS.$linkimagename)) {								
									   $d_sku=$product->getSku();	
									   $product->delete();
									   $message='File Does Not Exist. SKU: '.$d_sku;
									   array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
									   Mage::log($message,null,'cws_import_product_log.txt');									  
									   return true;
									}								
								
									$linkFileName = Mage::helper('downloadable/file')->moveFileFromTmp(
											Mage::getBaseDir('media') . DS . 'import',
											Mage_Downloadable_Model_Link::getBasePath(),
											$linkfile
									);
									
									try{
										$linkModel->setLinkFile($linkFileName)->save();	
									}catch(Exception $e){									
										array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));
										Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
									}
								}

								if($sampledata[0]=='file'){
									if (!file_exists(Mage::getBaseDir('media').DS. 'import'.DS.$sampleimagename)) {
									   $d_sku=$product->getSku();	
									   $product->delete();
									   $message='Sample File Does Not Exist. SKU: '.$d_sku;
									   array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));	
									   Mage::log($message,null,'cws_import_product_log.txt');
									   return true;
									}								
								
									$sampleFileName = Mage::helper('downloadable/file')->moveFileFromTmp(
											Mage::getBaseDir('media') . DS . 'import',
											Mage_Downloadable_Model_Link::getBaseSamplePath(),
											$samplefile
									);		
									try{
										$linkModel->setSampleFile($sampleFileName)->save();											
									}catch(Exception $e){
										array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));
										Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
									}
								}		
				}
		}
		
		if($product_type!='downloadable'){
        	try{
				$product->save();
			}catch(Exception $e){
				Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');							
				array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku'],'error_level'=>1));	
			}
		}

		$product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySku($importData['sku']));
		
		if($product_type=='configurable'){
			$superArray = $product-> getTypeInstance()->getConfigurableAttributesAsArray(); 
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');
			$adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
			$finalsuperattributepricing=$importData['super_attribute_value'];
			$SuperAttributePricingData = array();
			$FinalSuperAttributeData = array();
			$SuperAttributePricingData = explode('|',$finalsuperattributepricing);

			$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			foreach( $superArray AS $key => $val ) { 
					foreach( $val[ 'values' ] AS $keyValues => $valValues ) { 
						foreach($SuperAttributePricingData as $singleattributeData) {
							$FinalSuperAttributeData = explode(':',$singleattributeData);
							
							if("".$FinalSuperAttributeData[0] == $superArray[$key][ 'values' ][$keyValues][ 'label' ]) {
								if($new) {
									$insertPrice='INSERT into '.$prefix.'catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value) VALUES
											 ("'.$superArray[$key][ 'values' ][$keyValues][ 'product_super_attribute_id' ].'", "'.$superArray[$key][ 'values' ][$keyValues][ 'value_index' ].'", "'.$FinalSuperAttributeData[2].'", "'.$FinalSuperAttributeData[1].'");';
											$adapter->query($insertPrice);
								} else {
									  if($FinalSuperAttributeData[1] != "") {
																		  
												$finalpriceforupdate = $FinalSuperAttributeData[1];
												  
												$select_qry2 = $read->query("SELECT value_id FROM ".$prefix."catalog_product_super_attribute_pricing WHERE product_super_attribute_id = '".$superArray[$key][ 'values' ][$keyValues][ 'product_super_attribute_id' ]."' AND value_index = '".$superArray[$key][ 'values' ][$keyValues][ 'value_index' ]."'");
												$newrowItemId2 = $select_qry2->fetch();
												$db_product_id = $newrowItemId2['value_id'];
												if($db_product_id == "") {
												$insertPrice='INSERT into '.$prefix.'catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value) VALUES
														 ("'.$superArray[$key][ 'values' ][$keyValues][ 'product_super_attribute_id' ].'", "'.$superArray[$key][ 'values' ][$keyValues][ 'value_index' ].'", "'.$FinalSuperAttributeData[2].'", "'.$FinalSuperAttributeData[1].'");';
												$adapter->query($insertPrice);
												
												} else {
												$updatePrice="UPDATE ".$prefix."catalog_product_super_attribute_pricing SET pricing_value = '".$finalpriceforupdate."' WHERE value_id = '".$db_product_id."'";
												$adapter->query($updatePrice);
												}
										}								
								}
							}
						}
				 }
			}
		}else if($product_type=='grouped'){
			$import_custom_option=false;
			$products_links = Mage::getModel('catalog/product_link_api');		
			$group_product_id = $product->getId();
			 
			$simple_product_id_data = explode(",",$importData['grouped_product_sku']);
			foreach($simple_product_id_data as $groupedProd){                
				$product_id = Mage::getModel("catalog/product")->getIdBySku($groupedProd);
				if($product_id){
					$products_links->assign ("grouped",$group_product_id,$product_id); 
				}else{
					$message="Product does not exists SKU: ".$groupedProd;
					Mage::log($message,null,'cws_import_product_log.txt');										
					array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));	
				}
			}
				/* Added For Fix Frontend Issue */
				$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($group_product_id);
				$stockItem->setData('is_in_stock', 1);
				$stockItem->save();
		} 
		if(isset($importData['cws_tier_price']) && !empty($importData['cws_tier_price'])){
			$this->_tierPrice($product, $importData['cws_tier_price'], $store);			
		}

		if(isset($importData['cws_group_price']) && !empty($importData['cws_group_price'])) {

			$group_price=array();
			$group_price_data=explode(",",$importData['cws_group_price']);
			foreach($group_price_data as $gp){
				$gp_entry=explode("=",$gp);
				array_push($group_price,array ("website_id" =>$store->getWebsiteId(),"cust_group" =>$gp_entry[0],"price" =>$gp_entry[1]));
			}
			$product->setData('group_price',$group_price);
		}

		if($product->getPriceType()=='0'){
			$import_custom_option=false;
		}
 
		if(!isset($importData['cws_update_custom_option']) || $importData['cws_update_custom_option']=='0'){

			if(isset($custome_option_name[0]) || isset($custome_option_name[1]) || isset($custome_option_name[2]) || isset($custome_option_name[3]) || isset($custome_option_name[4]) || isset($custome_option_name[5]) || isset($custome_option_name[7])){

				if(isset($custom_options)){
					foreach ($product->getOptions() as $option) {
						$option->getValueInstance()->deleteValue($option->getId());
						$option->deletePrices($option->getId());
						$option->deleteTitles($option->getId());
						$option->delete();
					}
				}
			}
			
			if(isset($custom_options) && $custom_flag=="Cws" && $import_custom_option){
				if(count($custom_options)) {
				   foreach($custom_options as $option) {
					  try {
						$opt = Mage::getModel('catalog/product_option');
						$opt->setProduct($product);
						$opt->addOption($option);
						$opt->saveOptions();
					  }
					  catch (Exception $e) {
						Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');
						array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku']));				  
					  }
				   }
				}
			}
			
		}else{
	   		preg_match_all('/cws!(.*?):(.*?):\\d/', $subject, $custom_option_master_data, PREG_PATTERN_ORDER);			
			$options = $product->getProductOptionsCollection();			
			$custom_option_title_map=explode('|',$importData['cws_custom_option_title_mapping']);
			$custom_option_title_map_value=array();
			foreach($custom_option_title_map as $mapVal){			
				$mapTempVal=explode('~',$mapVal);
				$custom_option_title_map_value[$mapTempVal[0]]=$mapTempVal[1];	
			}
			
			foreach ($options as $o) { 
				$cos=array();
				$co=array();
			
				$title = $o->getTitle();
				$data_key=$this->customOptionLookUp($custom_option_master_data,$title);

				if (isset($custom_option_title_map_value[$title])) {
					$o->setProduct($product); 
					$o->setTitle($custom_option_title_map_value[$title]); 
					$o->setStoreId($store->getId());
				}

 				$optionType = $o->getType(); 				
				$found = false; 
				if(isset($importData[$data_key]))
				{
					$found=true;
				}
				
				$custom_options_val=explode('|',$importData[$data_key]);
				$i=0;				
				$values = $o->getValuesCollection(); 
										
				foreach ($values as $k => $v) { 

					$custom_option_val_single=explode(":",$custom_options_val[$i]);						
					if(isset($custom_option_val_single[0]) && $found){
						$v->setTitle($custom_option_val_single[0]);
					}						
					if(isset($custom_option_val_single[1]) && $found){
						$v->setPriceType($custom_option_val_single[1]);
					}
					if(isset($custom_option_val_single[2]) && $found){
						$v->setPrice(floatval($custom_option_val_single[2]));
					}
					if(isset($custom_option_val_single[3]) && $found){
						$v->setSku($custom_option_val_single[3]);
					}
					if(isset($custom_option_val_single[4]) && $found){
						$v->setSortOrder($custom_option_val_single[4]);
					}				
					if(isset($custom_option_val_single[5]) && $found){
						$v->setDetails($custom_option_val_single[5]);
					}												

					if($found){	
						$v->setStoreId($store->getId());
					}else{
						$v->setStoreId($v->getStoreId());
					}
					$v->setOption($o)->save();
					$cos[]=$v->toArray($co); 
					$i++;
				}
				$o->setData("values",$cos)->save();			
			}		
		}			
       
		if($product->getTypeId()=='configurable'){
			$is_required_status=true;
			$product->setHasOptions(true);
		}
		$product->setRequiredOptions($is_required_status);
		if($product && $importData['related_product_sku']!=null){
			$related_product_sku = $importData['related_product_sku'];
			$related_product_sku_single = explode('|', $related_product_sku);
			$related_product_position = explode('|', $importData['related_product_position']);
			
			$r_data = array();
			$z = 1;
			$r = 0;
			foreach($related_product_sku_single as $r_sku){
				$aRelatedProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $r_sku);
				if(isset($aRelatedProduct['entity_id'])){
					$r_data[$aRelatedProduct['entity_id']] = array('position' => $related_product_position[$r]);
					$r++;
					$z++;
				}else{
					$message="Product SKU:".$r_sku." does not exists. Can't assign to related product.";
					Mage::log($message,null,'cws_import_product_log.txt');
					array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));				  					
				}
				$z++;
			}
			$product->setRelatedLinkData($r_data);
		}

		if($product && $importData['crosssell_product_sku']!=null){
			$crosssell_product_sku = $importData['crosssell_product_sku'];
			$crosssell_product_sku_single = explode('|', $crosssell_product_sku);
			$crosssell_product_position = explode('|', $importData['crosssell_product_position']);
			$c_data = array();
			$z = 1;
			$c = 0;

			foreach($crosssell_product_sku_single as $c_sku){
				$aCrossesellProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $c_sku);
				
				if(isset($aCrossesellProduct['entity_id'])){
					$c_data[$aCrossesellProduct['entity_id']] = array('position' => $crosssell_product_position[$c]);
					$c++;
					$z++;
				}else{				
					$message="Product SKU:".$c_sku." does not exists. Can't assign to cross-sell product.";
					Mage::log($message,null,'cws_import_product_log.txt');												
					array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));
				}				

			}
			$product->setCrossSellLinkData($c_data);
		}		

		if($product && $importData['upsell_product_sku']!=null)
		{
			$upsell_product_sku = $importData['upsell_product_sku'];
			$upsell_product_sku_single = explode('|', $upsell_product_sku);
			$upsell_product_position = explode('|', $importData['upsell_product_position']);
			$u_data = array();
			$z = 1;
			$u = 0;
			foreach($upsell_product_sku_single as $u_sku)
			{
				$aUpesellProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $u_sku);
				if(isset($aUpesellProduct['entity_id'])){
					$u_data[$aUpesellProduct['entity_id']] = array('position' => $upsell_product_position[$u]);
					$u++;
					$z++;
				}else{				
					$message="Product SKU:".$u_sku." does not exists. Can't assign to up-sell product.";
					Mage::log($message,null,'cws_import_product_log.txt');
					array_push($this->_error,array('txt'=>$message,'product_sku'=>$importData['sku']));	
				}
			}
			$product->setUpSellLinkData($u_data);
		}

        $product->setStoreId($store->getId());
		try{
			if(isset($importData['img_label'])){
					$img_labels = explode('|',$importData['img_label']);
					$attributes = $product->getTypeInstance(true)->getSetAttributes($product);
					$gallery = $attributes['media_gallery'];
					$images = $product->getMediaGalleryImages();
					$i = 0;
					foreach ($images as $image) {
						$backend = $gallery->getBackend();
						$backend->updateImage($product,$image->getFile(),array('label' => $img_labels[$i]));	
						$i++;
					}
					$product->getResource()->saveAttribute($product, 'media_gallery');
				}
			
		$product->save();
	
		/* This Code is added by Simoni for fixed URL Rewrite Indexing issue. */
		//Mage_Catalog_Model_Indexer_Url::reindexAll();
		/* End of code */
	
		/* $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
		$stockItemId = $stockItem->getId(); // in case you need it
		$stockItem->setData('use_config_qty_increments', false);
		$stockItem->setData('use_config_enable_qty_inc', false);
		$stockItem->setData('use_config_enable_qty_increments', false);
		$stockItem->setData('enable_qty_increments', true);
		$stockItem->setData('qty_increments', 100);
		$product->save(); */
			
		Mage::unregister('product');
		Mage::unregister('current_product');

		}catch(Exception $e){
			Mage::unregister('product');
			Mage::unregister('current_product');
			Mage::log('Product SKU: '.$importData['sku']." ".$e->getMessage(),null,'cws_import_product_log.txt');						
			array_push($this->_error,array('txt'=>$e->getMessage(),'product_sku'=>$importData['sku']));	
		}
        return true;
	}
   
	public function customOptionLookUp($result,$option_title)
	{
		if (in_array($option_title, $result[1])) {			
			$key = array_search($option_title, $result[1]); 
			return $result[0][$key];
		}else{
			return null;
		}
		
	}
	 
	public function getPriceView($txt){
		if($txt=='price range'){
			return '0';
		}else{
			return '1';
		}
	} 

	public function getPriceType($txt){
		if($txt=='fixed'){
			return '1';
		}else{
			return '0';
		}
	} 
	
	private function _tierPrice(&$product, $tier_price_input = false, $store)
	{
		if (($tier_price_input) && !empty($tier_price_input)) {
            
            if(trim($tier_price_input) == 'CLEAR'){            
                $product->setTierPrice(array());
            } else {
                if($this->getBatchParams('append_tier_prices') == "true") { 
                	$tier_price_opt = $product->getTierPrice();
				} else {
                	$tier_price_opt = array();
				}
                
                $option_container = array();
                foreach($tier_price_opt as $key => $etp){
                    $option_container[intval($etp['price_qty'])] = $key;
                }
                
                $option_inset_str = explode('|',$tier_price_input);
								$options_toAdd = array();  
								$tiercnt=0;              
							foreach($option_inset_str as $option_single_str){
									if (empty($option_single_str)) continue;
									
									$tmp = array();
									$tmp = explode('=',$option_single_str);
									
									if ($tmp[1] == 0 && $tmp[2] == 0) continue;

									$options_toAdd[$tiercnt] = array(
										'website_id' => 0,
										'cust_group' => $tmp[0],
										'price_qty' => $tmp[1],
										'price' => $tmp[2],
										'delete' => ''
									);
													
									if(isset($option_container[intval($tmp[1])])){
										unset($tier_price_opt[$option_container[intval($tmp[1])]]);
									}
									$tiercnt++;
							}
                $options_toAdd =  array_merge($tier_price_opt, $options_toAdd);
                $product->setTierPrice($options_toAdd);
            }
        }
	}
}