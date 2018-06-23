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

class  CapacityWebSolutions_ImportProduct_Adminhtml_DownloadimagesController extends Mage_Adminhtml_Controller_Action
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
			$this->_addContent($this->getLayout()->createBlock('importproduct/adminhtml_downloadimages_edit'))
             ->_addLeft($this->getLayout()->createBlock('importproduct/adminhtml_downloadimages_edit_tabs'));
		}
			 
        $this->renderLayout();
	}
	
	public function uploadProfileAction() {
	
		$cat_dir = "media/import";
		if(!is_dir($cat_dir)){
			mkdir($cat_dir);
		}
		
		//$datetime = strtotime("now");
		$datetime = date('m-d-Y_h-i-s', time()); //Added Code by Parag
		
		$export_file_name = "import_images_".$datetime.".csv";
		$listheaader = array("store","websites","sku","image","small_image","thumbnail","gallery");
		$files = fopen("var/import/".$export_file_name,"w");
		fputcsv($files,$listheaader);
		fclose($files); 
	
		$file=$_FILES['downloadimg']['tmp_name'];
		$fp=fopen($file,"r");
		$ArrSourse = fgetcsv($fp);
		
		while($ArrSourse = fgetcsv($fp)) {
		
			$images =  explode("|",$ArrSourse[3]);
			$mainimg = '';
			$galleryimg = '';
			for($i=0;$i<count($images);$i++){
			
				$URL = urldecode($images[$i]);
				$image_name = (stristr($URL,'?',true))?stristr($URL,'?',true):$URL;
				$pos = strrpos($image_name,'/');
				$image_name = substr($image_name,$pos+1);
				$image = explode(".",$image_name);
				$extension = end($image);
				if(strtolower($extension) == 'jpg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif' || strtolower($extension) == 'jpeg'){
											
				 	$img_list_filename = Mage::getBaseDir('media') . DS . "import" . DS . $image_name;
					if (file_exists($img_list_filename)) {
						$path_parts = pathinfo($image_name);
						$image_name = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];
					}
								
					$content = file_get_contents($images[$i]);
					$fps = fopen($cat_dir."/".$image_name, "w");
					fwrite($fps, $content);
					fclose($fps); 
					if($i==0){
						$mainimg .= "/".$image_name;
					}else{
						if($i == (count($images)-1)){
							$galleryimg .= "/".$image_name;
						}else{	
							$galleryimg .= "/".$image_name."|";
						}	
					}	
				}else{
				
					Mage::log($ArrSourse[2]. " => ".$images[$i],null,'images.txt');  
				}
			} 
			
			$listimg = array($ArrSourse[0],$ArrSourse[1],$ArrSourse[2],$mainimg,$mainimg,$mainimg,$galleryimg);
			$files = fopen("var/import/".$export_file_name,"a");
			fputcsv($files,$listimg);
			fclose($files); 
		}
							
		$download_path=Mage::helper("adminhtml")->getUrl("adminhtml/Downloadimages/downloadExportedFile/",array("file"=>$export_file_name));		
		Mage::getSingleton("core/session")->addSuccess("Generated csv File : <b style='font-size:12px'><a target='_blank' href='".$download_path."' /'>".$export_file_name.'</a></b>'); 	
		$this->_redirectReferer();
	}
	
	
	public function downloadExportedFileAction(){
	
		$filename=Mage::app()->getRequest()->getParam('file');
        $filepath = Mage::getBaseDir('base').'/var/import/'.$filename;

        if (! is_file ( $filepath ) || ! is_readable ( $filepath )) {
            throw new Exception ( );
        }
        $this->getResponse ()
                ->setHttpResponseCode ( 200 )
                ->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true )
                ->setHeader ( 'Pragma', 'public', true )
                ->setHeader ( 'Content-type', 'application/force-download' )
                ->setHeader ( 'Content-Length', filesize($filepath) )
                ->setHeader ('Content-Disposition', 'attachment' . '; filename=' . basename($filepath) );
        $this->getResponse ()->clearBody ();
        $this->getResponse ()->sendHeaders ();
        readfile ( $filepath );
        exit;
	}
	protected function _isAllowed(){
		return true;
	}
}

?>