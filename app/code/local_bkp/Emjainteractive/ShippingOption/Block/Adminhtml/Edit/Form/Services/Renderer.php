<?php

class Emjainteractive_ShippingOption_Block_Adminhtml_Edit_Form_Services_Renderer
    extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('emjainteractive/shipingoption/edit/form/services.phtml');
    }

    /**
     * Get element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();

    }
}
