<?php

$installer = $this;
$installer->startSetup();

$adapter = $this->getConnection();
$select = $adapter->select()->from($this->getTable('admin/user'),
    array(
        new Zend_Db_Expr('null'),
        new Zend_Db_Expr('"default"'),
        new Zend_Db_Expr('0'),
        new Zend_Db_Expr('"' . Potato_LoginAsCustomer_Helper_Config::PERMISSION_ALLOW_FOR_USER . '"'),
        new Zend_Db_Expr('GROUP_CONCAT(user_id)')
    )
);
$query = $select->insertFromSelect($this->getTable('core/config_data'), $fields);
$this->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE)->query($query);

$installer->endSetup();