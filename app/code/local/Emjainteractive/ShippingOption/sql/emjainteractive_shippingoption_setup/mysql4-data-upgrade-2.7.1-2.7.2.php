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

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$tableName = $installer->getTable('emjainteractive_shippingoption/option');
$select = $installer->getConnection()->select()
    ->from($tableName);
$options = $installer->getConnection()->fetchAll($select);

$shippingMethods = array();
$shippingServices = array();

foreach ($options as $option) {
    if ($option['code'] == 'shipping_method') {
        $values = explode(',', $option['options']);
        foreach ($values as $val) {
            $val = trim($val);
            if (!empty($val)) {
                $shippingMethods[] = $val;
            }
        }
    }
    if ($option['code'] == 'shipping_service') {
        $values = explode(',', $option['options']);
        foreach ($values as $val) {
            $val = trim($val);
            if (!empty($val)) {
                $shippingServices[] = $val;
            }
        }
    }
}
$shippingMethods = array_unique($shippingMethods);
$shippingServices = array_unique($shippingServices);

foreach ($options as $option) {
    if ($option['code'] == 'shipping_method') {
        $installer->getConnection()->update(
            $tableName, array(
                'options' => serialize($shippingMethods)
            ),
            $installer->getConnection()->quoteInto('option_id = ?', $option['option_id'])
        );
    } else if ($option['code'] == 'shipping_service') {
        $serviceOptions = array();
        foreach ($shippingServices as $service) {
            $serviceOptions[$service] = $shippingMethods;
        }
        $installer->getConnection()->update(
            $tableName, array(
                'options' => serialize($serviceOptions)
            ),
            $installer->getConnection()->quoteInto('option_id = ?', $option['option_id'])
        );
    } else if (in_array($option['type'], array('select', 'radio'))) {
        $values = explode(',', $option['options']);
        $updatedValues = array();
        foreach ($values as $val) {
            $val = trim($val);
            if (!empty($val)) {
                $updatedValues[] = $val;
            }
        }
        $updatedValues = array_unique($updatedValues);
        $installer->getConnection()->update(
            $tableName, array(
                'options' => serialize($updatedValues)
            ),
            $installer->getConnection()->quoteInto('option_id = ?', $option['option_id'])
        );
    }
}
