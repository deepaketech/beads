<?php

     /**
      * @Module    Magegeeks_Registration
      * @Developer Deepak Mankotia
      * @Email     deepakmankotiacse@gmail.com
      */
class Magegeeks_Hideprice_Model_Observer
{


     public function customerlogin($observer)
     {
			if($observer->getCustomer()->getStatus_type()):
			
				return true;
			else:
					
					Mage::getSingleton('customer/session')->logout();
					Mage::getSingleton('customer/session')->addError('This account is not activated yet.');
					
					return false;
			endif;
			
     }

}



