<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('customer/customer_group')} ADD COLUMN `minimum_order_amount`int(11) NOT NULL;");
$installer->endSetup();
