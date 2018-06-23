<?php
class CapacityWebSolutions_ImportProduct_Block_Adminhtml_System_Config_Form_Fieldset_Support_Support
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		$html = $this->_getHeaderHtml($element);
        $html .= '<h4>Need help? Please contact us at <a href="mailto:support@magebees.com">support@magebees.com</a>
 or you can open the ticket from <a href="http://support.magebees.com/" target="_blank">here</a></h4>';
		$html .= $this->_getFooterHtml($element);
        return $html;
    }
}
