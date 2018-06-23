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

class Emjainteractive_ShippingOption_Block_Renderer_Select extends Emjainteractive_ShippingOption_Block_Renderer_Abstract
{
    protected function _renderLabel()
    {
        $req = $this->_getIsRequired() ? true : false;
        if ($req) {
            $html = '<label class="required" for="'. $this->_getElementId() .'"><em>*</em>' . $this->getOption()->getLabel() . '</label>&nbsp;';
        } else {
            $html = '<label for="'. $this->_getElementId() .'">' . $this->getOption()->getLabel() . '</label>&nbsp;';
        }
        
        return $html;
    }
    
    protected function _renderElement()
    {
        $onChange = '';
        $req = $this->_getIsRequired() ? true : false;
        $value = $this->getValue();
        $val = is_array($value) ? $value[$this->getCarrier()][$this->getOption()->getCode()] : $this->getOption()->getDefaultValue();
        if ($this->getOption()->getUpdateOnChange()) {
            $onChange = ' onchange="sendUpdateOrderRequest()"';
        }

        if ($req) {
            $html = '<select class="required-entry" name="' . $this->_getElementName() . '" id="' . $this->_getElementId() . '"' . $onChange . '>';
        } else {
            $html = '<select name="' . $this->_getElementName() . '" id="' . $this->_getElementId() . '"' . $onChange . '>';
        }

        if ($this->getOption()->getCode() != 'shipping_service') {
            foreach ($this->getOption()->getOptions() as $option) {
                $selected = '';
                if (empty($val)) {
                    $val = $option;
                }
                if ($val == $option) {
                    $selected = 'selected="selected"';
                }
                $html .= '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
            }
        }
        $html.= '</select>';
        if ($this->getOption()->getCode() == 'shipping_service') {
            $jsonEncodedValues = json_encode($this->getOption()->getOptions());
            $selectedService = $this->jsQuoteEscape($val);
            $html .= <<<JS
<script type="text/javascript">
    var shippingServiceOptions = $jsonEncodedValues;
    var selectedShippingService = '$selectedService';

    function fillShippingServices(carrier) {
        var option;
        $('{$this->_getElementId()}').select('option').invoke('remove');
        for (key in shippingServiceOptions) {
            if (carrier != undefined) {
                if (shippingServiceOptions.hasOwnProperty(key) && shippingServiceOptions[key].indexOf(carrier) >= 0) {
                    option = document.createElement('option');
                    option.setAttribute('value', key);
                    option.innerHTML = key;
                    $('{$this->_getElementId()}').appendChild(option);
                }
            } else {
                if (shippingServiceOptions.hasOwnProperty(key)) {
                    option = document.createElement('option');
                    option.setAttribute('value', key);
                    option.innerHTML = key;
                    $('{$this->_getElementId()}').appendChild(option);
                }
            }
        }
        $('{$this->_getElementId()}').value = selectedShippingService;
    }

    if ($('umosaco_shipping_method') != undefined) {
        $('umosaco_shipping_method').observe('change', function(event) {
            var elm = Event.element(event);
            if (elm.selectedIndex >= 0) {
                var carrier = elm.options[elm.selectedIndex].innerHTML;
                fillShippingServices(carrier);
            }
        });
        if ($('umosaco_shipping_method').selectedIndex >= 0) {
            fillShippingServices($('umosaco_shipping_method').options[$('umosaco_shipping_method').selectedIndex].innerHTML);
        }
    } else {
        fillShippingServices();
    }
</script>
JS;
        }


        return $html;
    }
}
