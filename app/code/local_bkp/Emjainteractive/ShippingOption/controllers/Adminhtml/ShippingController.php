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

class Emjainteractive_ShippingOption_Adminhtml_ShippingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Options list
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('emjainteractive_shippingoption/adminhtml_option'));
        $this->renderLayout();
    }

    /**
     * New option action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit option
     */
    public function editAction()
    {
        $option = Mage::getModel('emjainteractive_shippingoption/option')->load($this->getRequest()->getParam('option_id'));
        if ($option && $option->getId()) {
            Mage::register('option_data', $option);
        }

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('emjainteractive_shippingoption/adminhtml_edit'));
        $this->renderLayout();
    }

    /**
     * Save option action
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('option_id');
            $model = Mage::getModel('emjainteractive_shippingoption/option')->load($id);
            $options = $this->getRequest()->getPost('options', array());
            if (is_array($options)) {
                unset($options['__empty']);
            }
            if ($model && $model->getCode() == 'shipping_service') {
                $newOptions = array();
                foreach ($options as $option) {
                    $newOptions[$option['name']] = $option['carrier'];
                }
                $options = $newOptions;
            }
            $model->addData(array(
                'code'      => trim($this->getRequest()->getPost('code')),
                'label'     => trim($this->getRequest()->getPost('label')),
                'type'      => trim($this->getRequest()->getPost('type')),
                'apply_to'  => $this->getRequest()->getPost('apply_to'),
                'default_value' => trim($this->getRequest()->getPost('default_value')),
                'sort_order'=> trim($this->getRequest()->getPost('sort_order')),
                'options'   => $options,
                'is_required'   => $this->getRequest()->getPost('is_required'),
            ));

            try {
                $model->save();
                $this->_getSession()->addSuccess($this->__('Option was successfully saved'));
                $this->_getSession()->setOptionData(false);
                $this->_redirect('*/*/index');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setOptionData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('option_id' => $id));
                return;
            }
        }

        $this->_redirect('*/*/*');
    }


    /**
     * Delete option action
     *
     * @return $this|Mage_Core_Controller_Varien_Action|void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('option_id')) {
            try {
                $model = Mage::getModel('emjainteractive_shippingoption/option')->load($id);
                if ($model) {
                    $this->_getSession()->addError($this->__('These options cannot be deleted.'));
                    return $this->_redirect('*/*/index');
                }
                $model->delete();
                $this->_getSession()->addSuccess($this->__('Option was successfully deleted.'));
                return $this->_redirect('*/*/index');
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('option_id' => $id));
                return;
            }
        }
        $this->_getSession()->addError($this->__('Unable to find option to delete.'));
        return $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return true;
    }
}
