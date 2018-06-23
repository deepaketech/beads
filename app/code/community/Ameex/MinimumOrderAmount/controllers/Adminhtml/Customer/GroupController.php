<?php 
require_once 'Mage/Adminhtml/controllers/Customer/GroupController.php';
class Ameex_MinimumOrderAmount_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Customer_GroupController 
 {
     public function saveAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $customerGroup->load((int)$id);
        }

        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        if ($taxClass) {
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');
                //replace "code" with the name of the field
                $minimumOrderAmount = (string)$this->getRequest()->getParam('minimum_order_amount');

                if (!empty($customerGroupCode)) {
                    $customerGroup->setCode($customerGroupCode);
                }

                $customerGroup->setTaxClassId($taxClass);
                $customerGroup->setMinimumOrderAmount($minimumOrderAmount)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }

    }

 }  
