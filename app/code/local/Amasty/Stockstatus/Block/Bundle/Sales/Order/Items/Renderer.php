<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Bundle_Sales_Order_Items_Renderer extends Mage_Bundle_Block_Sales_Order_Items_Renderer
{
    public function getValueHtml($item)
    {
        $html = parent::getValueHtml($item);

        /* Amasty Code start*/
        $status = Mage::helper('amstockstatus')->getStockStatusByIdInCart(
            $item->getProductId()
        );
        if ($status) {
            $html .= ' ( ' . $status . ' ) ';
        }
        /*Amasty code end*/
        return $html;
    }
}