<?php

 /***************************************************************************
	Extension Name 	: Import Export Products Extension for Simple Products | Configurable Products | Bundle Products | Group    Products | Downloadable Products
	Extension URL   : http://www.magebees.com/magento-import-export-products-extension.html 
	Copyright  		: Copyright (c) 2015 MageBees, http://www.magebees.com
	Support Email   : support@magebees.com 
 ***************************************************************************/ 

ini_set('max_execution_time', 360000);
ini_set('memory_limit', '1024M');
error_reporting(0);

class  CapacityWebSolutions_ImportProduct_Adminhtml_ImportproductsController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
	{
		$this->loadLayout()
			->_setActiveMenu('cws')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			return $this;
	} 
	
	public function indexAction() {
		$this->_initAction();
		if($data==null){
			$this->_addContent($this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit'))
             ->_addLeft($this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit_tabs'));
		}
        $this->renderLayout();
	}
	
	public function uploadProfileAction() {
	$this->_initAction()->renderLayout();
		if($this->getRequest()->getPost())
    	{
			try
        	{
           		 $filename = $_FILES['file_1']['name'];  
				 $newname=explode('.',$filename);
				 $filename1=$newname[0]; 
					$uploader = new Varien_File_Uploader('file_1');
					$uploader->setAllowedExtensions(array('csv','xml'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					//$date_var=time();
					$date_var = date('m-d-Y_h-i-s', time()); //Added Code by Parag
					$filename="import-".$date_var."_".$filename;
					$path = Mage::getBaseDir('var') . DS .('import');
					$full_path = $path . '/' . $filename;
					if(!file_exists($full_path)) {
						$uploader->save($path, $filename);
						Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('File : '.$filename.' Successfully Uploaded.'));
						$this->_redirect('*/*/index');
					}else{
							$nm=(rand(20000000,29999999));
							$name=$filename1.'_201303'.$nm.'_1.csv';
							$uploader->save($path, $name);
							Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('File : '.$name.' Successfully Uploaded.'));
							$this->_redirect('*/*/index');
						}
        	}
       		 catch(Exception $e)
       		 {
           		 Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				 $this->_redirect('*/*/index');
            	return;
       		 }
		}
	}
	
	public function validationlogAction()
    {
	    $this->loadLayout();
		$this->getResponse()->setBody(
        $this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit_tab_validationlog')->toHtml());
    }
	public function exportValidationCsvAction(){
	
		  $fileName   = 'productValidationLogCSV.csv';
		  $content    = $this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit_tab_validationlog')->getCsvFile();
		  $this->_prepareDownloadResponse($fileName, $content);
	}

	public function importlogAction()
    {
       $this->loadLayout();
       $this->getResponse()->setBody(
       $this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit_tab_importlog')->toHtml());
    }
	public function exportImportCsvAction()
	{
		  $fileName   = 'productImportLogCSV.csv';
		  $content    = $this->getLayout()->createBlock('importproduct/adminhtml_importproducts_edit_tab_importlog')->getCsvFile();
		  $this->_prepareDownloadResponse($fileName, $content);
	}
	
	public function validateAction()
    {
		$next_step=true;
		if($this->getRequest()->getParam('importfiletype')){
			$model=Mage::getModel('importproduct/validator');
		}else{
			$next_step=false;
		}
		if(Mage::app()->getRequest()->getParam('clearOldData')=='true'){
			$collection = Mage::getResourceModel('importproduct/profiler')->truncate();
		}
		$url=$model->setProfilerData();
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('url'=>$url)));
    }
	protected function _isAllowed(){
		return true;
	}
}
?>