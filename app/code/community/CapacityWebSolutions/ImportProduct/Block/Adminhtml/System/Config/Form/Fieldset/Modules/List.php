<?php
class CapacityWebSolutions_ImportProduct_Block_Adminhtml_System_Config_Form_Fieldset_Modules_List
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		$html = $this->_getHeaderHtml($element);
        $html .= '	<table cellspacing="0" class="form-list"><tbody><tr><td class="label"><label for="CapacityWebSolutions_ImportProduct">Current Version</label></td><td class="value">v2.3.2</td></tr></tbody></table>';
		
		$html .= $this->_getFooterHtml($element);
        return $html;
    }
}
