<?php 
/**
 * Mas_Mascartaddcsv extension by Makarovsoft.com
 * 
 * @category   	Mas
 * @package		Mas_Mascartaddcsv
 * @copyright  	Copyright (c) 2014
 * @license		http://makarovsoft.com/license.txt
 * @author		makarovsoft.com
 */
/**
 * Test helper
 *
 * @category	Mas
 * @package		Mas_Mascartaddcsv
 * 
 */
class Mas_Mascartaddcsv_Helper_Test extends Mage_Core_Helper_Abstract{
	/**
	 * check if breadcrumbs can be used
	 * @access public
	 * @return bool
	 * 
	 */
	public function getUseBreadcrumbs(){
		return Mage::getStoreConfigFlag('mascartaddcsv/test/breadcrumbs');
	}
}