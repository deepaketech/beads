<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Block_Field_Keyrenderer extends  Mage_Adminhtml_Block_System_Config_Form_Field
{
  
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		  $model = Mage::getModel('importproduct/profile');	
  		  $headers = array(
				'Content-Type: text/xml; charset=utf-8'
		   );

		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL, $model->getWebServiceURL());
		  curl_setopt($ch, CURLOPT_POST, FALSE);
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		  if (($result = curl_exec($ch)) === FALSE) {
		  } else 
		  {
			$xml = simplexml_load_string($result);			
			if($xml->cws->status==1){
			$element->setDisabled('disabled');		
			}
		}		
		curl_close($ch);
		return parent::_getElementHtml($element);  		 
	}	
}
?>