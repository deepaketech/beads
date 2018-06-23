<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */


class Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Facebook
    extends Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Abstract
{
    /**
     * @return string
     */
    protected function _getNoteHtml()
    {
        $html = '<div class="ajaxlogin-facebook">'
            . '<div class="title">'
            . $this->__('Please make sure the Facebook App is configured properly. Follow the simple steps below:')
            . '</div>'
            . '<ol>'
            . '<li>' . $this->__('1. Add your App Domain') . '</li>'
            . '<li>' . $this->__('2. Insert the Site URL') . '</li>'
            . '<li>' . $this->__('3. Fill in the "Valid OAuth redirect URIs"') . '</li>'
            . '</ol>'
            . '<div class="title">' . $this->__(
                'For for more information please follow the User Guide <a  target="_blank" href="%s">here</a>',
                Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Abstract::GUIDE_LINK
            ) . '</div>'
            . '<div class="image"><img src="' . $this->getSkinUrl('amasty/amajaxlogin/facebook.jpg') .'"></div>'
            . '</div>';

        return $html;
    }
}
