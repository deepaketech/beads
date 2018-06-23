<?php

class MageMill_SuperBreadcrumbs_Block_SuperBreadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
	protected function _toHtml()
	{
		$cat_id = "";
		$store_id = Mage::app()->getStore()->getStoreId();
		$enabled = Mage::getStoreConfig('superbreadcrumbs/general/enabled', $store_id);

		if($enabled)
		{
			if(Mage::registry('current_product'))
			{
				$displayhome = Mage::getStoreConfig('superbreadcrumbs/product/displayhome', $store_id);
				$displaytitle = Mage::getStoreConfig('superbreadcrumbs/product/displaytitle', $store_id);

				$product_id = Mage::registry('current_product')->getId();
				
				if($product_id)
				{
					$_product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id);
					$crumbs = array();

					$categoryIds = $_product->getCategoryIds();
					$root_category = Mage::app()->getStore()->getRootCategoryId();
					$home = $this->_crumbs['home'];

					if($displayhome == 'text')
					{
		         		$home['link'] = NULL;
		     		}

					$i = 0;
					foreach($categoryIds as $cat_id)
					{
			         	$category = Mage::getModel('catalog/category')->load($cat_id);
					    $cat_path = explode('/', $category->getPath());

						if(in_array($root_category, $cat_path))
						{
						    foreach($cat_path as $path)
						    {
						    	if($path != $root_category && $path != 1)
						    	{
						    		$cur_category = Mage::getModel('catalog/category')->load($path);

						    		if($cur_category->getMagemillSuperbreadcrumbs() !== '0')
						    		{
						    			$curcat_name = $cur_category->getName();
						    			$curcat_url =  $this->getBaseUrl().$cur_category->getUrlPath();

						    			$crumbs[$i]['category'.$path] = array('label'=>$curcat_name, 'title'=>'', 'link'=>$curcat_url,'first'=>'false','last'=>'false','readonly'=>'');
						    		}
						    	}
						    }

					    	$i++;
						}
					}

					if(count($crumbs) == 0)
					{
						if($displaytitle)
						{
							$crumbs[0]['current'] = array('label' => $_product->getName(), 'title' => '', 'link' => '', 'first' => false, 'last' => true);
						}

						if($displayhome == 'link' || $displayhome == 'text')
						{
							array_unshift($crumbs[0],$home);
						}
					}
					else
					{
						foreach($crumbs as $key => $value)
						{
							if($displaytitle)
							{
						 		$crumbs[$key]['current'] = array('label' => $_product->getName(), 'title' => '', 'link' => '', 'first' => false, 'last' => true);
						 	}

						    if($displayhome == 'link' || $displayhome == 'text')
						 	{
						    	array_unshift($crumbs[$key],$home);
						    }
						}
					}
					
					if(count($crumbs) == 0)
					{
						$this->_crumbs = NULL;
					}
					else
					{
						$this->_crumbs = $crumbs;
					}  
				}
			}
			elseif(Mage::registry('current_category'))
			{
				$displayhome = Mage::getStoreConfig('superbreadcrumbs/category/displayhome', $store_id);
				$displaytitle = Mage::getStoreConfig('superbreadcrumbs/category/displaytitle', $store_id);

				if(is_array($this->_crumbs))
				{
					reset($this->_crumbs);
					$this->_crumbs[key($this->_crumbs)]['first'] = true;
					end($this->_crumbs);
					$this->_crumbs[key($this->_crumbs)]['last'] = true;
				}

				$crumbs[0] = $this->_crumbs;
		   	

				if(!$displaytitle)
		     	{
		     		$crumbs[0]['current'] = NULL;
		     	}

		     	if($displayhome == 'no')
		     	{
			    	unset($crumbs[0]['home']);
			    }

				if($displayhome == 'text')
				{
		        	$crumbs[0]['home']['link'] = NULL;
		     	}

				$this->_crumbs = $crumbs;
			}
			else
			{
				$displayhome = Mage::getStoreConfig('superbreadcrumbs/other/displayhome', $store_id);
				$displaytitle = Mage::getStoreConfig('superbreadcrumbs/other/displaytitle', $store_id);

				if(is_array($this->_crumbs))
				{
					reset($this->_crumbs);
					$this->_crumbs[key($this->_crumbs)]['first'] = true;
					end($this->_crumbs);
					$this->_crumbs[key($this->_crumbs)]['last'] = true;
				}

		   		$crumbs[0] = $this->_crumbs;

			   	if(!$displaytitle)
		     	{
		     		$crumbs[0]['current'] = NULL;
		     	}

		     	if($displayhome == 'no')
		     	{
			    	unset($crumbs[0]['home']);
			    }

				if($displayhome == 'text')
				{
					$crumbs[0]['home']['link'] = NULL;
				}

				$this->_crumbs = $crumbs;
			}

			$this->assign('crumbs', $this->_crumbs);
		}

		return parent::_toHtml();
	}
}