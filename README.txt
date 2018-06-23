MageCoders.com is your one stop source for all things Magento!  We develop some of the finest extensions for Magento and promise to deliver a quality product that will integrate
perfectly into your new or existing e-commerce storefront.  

Thank you for your purchase!  Please see installation instructions below.

Version : 2.0.1


=========== INSTALLATION ======================

1) Unzip QuickOrder.zip file, copy and paste all contents to your Magento Root dir.

2) Go to Magento Admin >  Cache Management and clear all cache.

3) Go to System > Configuration, click on "Quick Order" under "Magecoders Extension" Tab and enable it, enjoy great features.

4) To dispaly quickorder in CMS page or static block, first set Frontend Display = CMS page in Quickorder Configuration.  After
    that place below code in your page or static block to display Quick Order Form.

    To display Form in CMS page or Static block : 
        {{block type="quickorder/form" name="quickorder.form" template="quickorder/form.phtml"}}

    To display Form in .phtml file :
         <?php echo $this->getLayout()->createBlock('quickorder/form')->setTemplate('quickorder/form.phtml')->toHtml();  ?>
	



=========== FAQ ======================

1)  404 error on quick order configuration page.
Ans:  Please logout and login again to admin, after that click on quick order configuration tab, and you are done !!.


=========== Support ======================
If you have any questions or suggestions kindly contact us at : info@magecoders.com

- This version support only Simple products.
- No support for custom option in simple products.
- For Support of Configurable products please upgrade to Pro version.

=========RELEASE NOTES======================
Version 1.0.3
- Multi Store support added to display products in search suggestion for specific stores only
- Added fix for product add to cart.

Version 2.0.0
- New featured added to display quickorder form in cms page or static block.
Version 2.0.1
- Add new Item input box alignment fixed





