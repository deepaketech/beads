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
class Bss_ReorderProduct_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const COUNT = 1;
	/*
	* Function resize image
	* @param $path/to/image, $width, $height, $crop
	* Return String
	*/
	function resizeImage($imageUrl, $w, $h) {
			// create folder
			 if(!file_exists("./media/catalog/category/resize"))     mkdir("./media/catalog/category/resize",0777);
			 // get image name
			 $imageName = substr(strrchr($imageUrl,"/"),1);

			 // resized image path (media/catalog/category/resized/IMAGE_NAME)
			 $imageResized = Mage::getBaseDir('media').DS.'catalog'.DS.'category'.DS.'resize'.DS.'_w'.$w.'_h'.$h.'_'.$imageName;

			 // changing image url into direct path
			 $dirImg = Mage::getBaseDir().str_replace("/",DS,strstr($imageUrl,'/media'));

			 // if resized image doesn't exist, save the resized image to the resized directory
			 if (!file_exists($imageResized)&&file_exists($dirImg)) :
			 $imageObj = new Varien_Image($dirImg);
			 $imageObj->constrainOnly(TRUE);
			 $imageObj->keepAspectRatio(TRUE);
			 $imageObj->keepFrame(FALSE);
			 $imageObj->resize($w, $h);
			 $imageObj->save($imageResized);
			 endif;

			 $newImageUrl = Mage::getBaseUrl('media').'catalog/category/resize/'.'_w'.$w.'_h'.$h.'_'.$imageName;
			return $newImageUrl;
	}
}