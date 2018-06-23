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
class Bss_ReorderProduct_Model_Config_Option extends Mage_Core_Model_Config_Data
{
    const TYPE_PRODUCT_NAME = 1;
	const TYPE_PRODUCT_PRICE   = 2;
	const TYPE_PRODUCT_RECENT   = 3;
    /**
    * Get possible sharing configuration options
    *
    * @return array
    */
    public function toOptionArray()
    {
        return array(
            self::TYPE_PRODUCT_NAME  => Mage::helper('bssreorderproduct')->__('Name'),
			self::TYPE_PRODUCT_PRICE => Mage::helper('bssreorderproduct')->__('Price'),
			self::TYPE_PRODUCT_RECENT => Mage::helper('bssreorderproduct')->__('Recent Order'),
        );
    }

}
