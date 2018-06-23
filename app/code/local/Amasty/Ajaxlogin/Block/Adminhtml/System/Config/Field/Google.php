<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */


class Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Google
    extends Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Abstract
{
    /**
     * @return string
     */
    protected function _getNoteHtml()
    {
        $html = '<div class="ajaxlogin-google">'
            . '<div class="title">' . $this->__('Please make sure the Google App is configured properly:') . '</div>'
            . '<div class="title">' . $this->__(
                'For for more information please follow the User Guide <a  target="_blank" href="%s">here</a>',
                Amasty_Ajaxlogin_Block_Adminhtml_System_Config_Field_Abstract::GUIDE_LINK
            ) . '</div>'
            . '<div class="image"><img src="' . $this->getSkinUrl('amasty/amajaxlogin/google.jpg') .'"></div>'
            . '</div>';

        return $html;
    }
}
