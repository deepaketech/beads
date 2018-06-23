<?php

class Potato_LoginAsCustomer_Helper_Config extends Mage_Core_Helper_Abstract
{
    const PERMISSION_ALLOW_FOR_USER = 'po_loginascustomer/permission/allow_for_users';

    static function getAllowedUsers()
    {
        $configData = Mage::getStoreConfig(self::PERMISSION_ALLOW_FOR_USER);
        if ($configData) {
            $configData = explode(',', $configData);
        } else {
            $configData = array();
        }
        return $configData;
    }

    static function getIsAllowed()
    {
        if (in_array(Mage::getSingleton('admin/session')->getUser()->getUserId(), self::getAllowedUsers())) {
            return true;
        }
        return false;
    }
}