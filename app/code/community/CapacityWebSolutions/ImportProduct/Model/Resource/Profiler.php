<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

class CapacityWebSolutions_ImportProduct_Model_Resource_Profiler extends Mage_Core_Model_Mysql4_Abstract{
    protected function _construct()
    {
        $this->_init('importproduct/profiler', 'profiler_id');
    }
	public function truncate() {
		$this->_getWriteAdapter()->query('TRUNCATE TABLE '.$this->getMainTable());
		return $this;
	}
	
	public function insertMultipleProduct($rows){	
        $write = $this->_getWriteAdapter();
        $write->insertMultiple($this->getMainTable(), $rows);
	}



}