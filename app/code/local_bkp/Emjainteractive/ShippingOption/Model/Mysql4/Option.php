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

class Emjainteractive_ShippingOption_Model_Mysql4_Option extends Mage_Core_Model_Mysql4_Abstract 
{
    protected $_serializableFields   = array('options' => array(null, array()));

    protected function _construct()
    {
        $this->_init('emjainteractive_shippingoption/option', 'option_id');
    }
    
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('code'),
            'title' => Mage::helper('emjainteractive_shippingoption')->__('Option with the same code')
        ));
        return $this;
    }

    protected function _unserializeField(Varien_Object $object, $field, $defaultValue = null)
    {
        if (in_array($object->getData('type'), array('select', 'radio'))) {
            return parent::_unserializeField($object, $field, $defaultValue);
        }
        return $this;
    }

    protected function _serializeField(Varien_Object $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        if (in_array($object->getData('type'), array('select', 'radio'))) {
            if (!is_array($object->getData($field))) {
                $opt = explode(',', $object->getData($field));
                $vals = array();
                foreach ($opt as $o) {
                    $o = trim($o);
                    if (!empty($o)) {
                        $vals[] = $o;
                    }
                }
                $object->setData($field, array_unique($vals));
            }
            return parent::_serializeField($object, $field, $defaultValue, $unsetEmpty);
        }
        return $this;
    }
}
