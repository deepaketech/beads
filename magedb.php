<?php
 
//increase execution time
	ini_set('max_execution_time', 18000); //1800 seconds = 30 minutes
 
	//require Magento
	require_once 'app/Mage.php';
	$app = Mage::app('admin');
	umask(0);
 
	//set error reporting
	error_reporting(E_ALL & ~E_NOTICE);
	Mage::setIsDeveloperMode(true);
 
	//do backup
	try {
		$backupDbHelper = Mage::getModel('backup/db');
		//create backup instance, set certain options
		$backup = Mage::getModel('backup/backup')
		->setTime(time())
		->setType('db')
		->setPath(Mage::getBaseDir("var") . DS . "backups");
		//do actual backup
		$backupDbHelper->createBackup($backup);
		//return success
		//print 'Backup successfully created';
 
	} 
	catch (Exception $e) {
		//log exception magento and print to screen
		Mage::logException($e->getMessage());
		//print 'Error while creating backup: ' . $e->getMessage();
	}