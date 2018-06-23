<?php

class Potato_LoginAsCustomer_Model_Source_Permission_User
{
    static public function toOptionArray()
    {
        $collection = Mage::getResourceModel('admin/user_collection');
        $options = array();
        foreach($collection as $user) {
            $options[] = array(
                'value' => $user->getId(),
                'label' => $user->getUsername()
            );
        }
        return $options;
    }
}