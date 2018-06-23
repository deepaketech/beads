<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */


class Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Abstract
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const GUIDE_LINK = 'https://amasty.com/media/user_guides/quick_ajax_login_user_guide.pdf';
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        $html .= $this->_getNoteHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getNoteHtml()
    {
        return '';
    }

}
