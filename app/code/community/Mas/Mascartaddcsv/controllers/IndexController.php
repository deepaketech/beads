<?php
class Mas_Mascartaddcsv_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function preDispatch()
    {
        parent::preDispatch();        
    }

    public function processAction()
    {
    	$data = $this->getRequest()->getPost('location');
    	$loc = Mage::getBaseDir('media').DS.'location'.DS.'file';
		$imageName = Mage::getBaseDir('media').DS.'location'.DS.'file' .  $this->_uploadAndGetName('file', $loc, $data);
		
    	$csv = new Varien_File_Csv();
    	$csv->setDelimiter(Mage::getStoreConfig('mascartaddcsv/customer/column_delimiter'));
		$data = $csv->getData($imageName);
	
		$d = array();
		foreach ($data as $d2) {
                      $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',trim($d2[0]));
		//$d[trim($d2[0])] = $d2;
		if($_product){
		$d[trim($_product->getSku())] = $d2;
		}
                       // $d[trim($_product->getSku())] = $d2;
		}
		
		Mage::getModel('mascartaddcsv/add')->add($d);
		$this->_redirect('checkout/cart');
    }
    
	protected function _uploadAndGetName($input, $destinationFolder, $data){
		try{
			if (isset($data[$input]['delete'])){
				return '';
			}
			else{
				$uploader = new Varien_File_Uploader($input);
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				$uploader->setAllowCreateFolders(true);
				$result = $uploader->save($destinationFolder);
				return $result['file'];
			}
		}
		catch (Exception $e){
			if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY){
				throw $e;
			}
			else{
				if (isset($data[$input]['value'])){
					return $data[$input]['value'];
				}
			}
		}
		return '';
	}
    
	public function indexAction()
	{
	
		if (!Mage::getStoreConfigFlag('mascartaddcsv/customer/enabled')) {
            $this->norouteAction();
            return;
        } 
        
        $session = Mage::getSingleton('customer/session');
        if (!$session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        
        $block = $this->getLayout()->getBlock('customer_account_navigation');
        
        if ($block) {
            $block->setActive('mascartaddcsv/index');
        }
        
        $this->_title(Mage::helper('mascartaddcsv')->__('Bulk Add To Cart From CSV'));
			 
        $this->renderLayout();
	}
}