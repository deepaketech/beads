<?php

class MageAssist_FpcInCart_Model_Replace extends Varien_Object
{
    /**
     * @var array
     */
    protected $_actions = [
        'catalog_category_view',
        'catalog_product_view',
        'bssreorderproduct_list_index',
        'amshopby_index_index',
        'catalogsearch_result_index',
    ];

    /**
     * @var array
     */
    protected $_products = [];

    /**
     * @param string $content
     * @param string $action
     * @return string
     */
    public function replaceContent($content, $action)
    {
        $this->fillProducts(true);

        return (true === in_array($action, $this->_actions))
            ? $this->processContent($content)
            : $content;
    }

    /**
     * @param string $content
     * @param bool|null $isAjax
     * @return string
     */
    public function replaceCache($content, $isAjax = null)
    {
        $this->fillProducts(false);

        if (true === $isAjax) {
            try {
                $json = json_decode($content, true);
                if (true === isset($json['page'])) {
                    $json['page'] = $this->processContent($json['page']);
                    return json_encode($json);
                }
            } catch (\Exception $e) {
            }
        }

        return $this->processContent($content);
    }

    /**
     * @param string $content
     * @return string
     */
    protected function processContent($content)
    {
        $content = preg_replace_callback(
            '~<a ([^>]+) (data-product-id="(\d+)" data-replace-incart="([^"]+)")>(.+)</a>~i',
            function ($matches) {
                if (false === $this->isProductInQuote($matches[3])) {
                    return $matches[0];
                }

                $res = '<a ';
                $res .= preg_replace('~class="([^"]+)"~i', 'class="$1 incart"', $matches[1]);
                $res .= ' ' . $matches[2] . '>';
                if (false !== strpos($matches[5], '<i class="icon-cart"></i>')) {
                    $res .= '<i class="icon-cart"></i>';
                }
                $res .= '<span> ' . $matches[4] . '</span></a>';

                return $res;
            },
            $content
        );

        $content = preg_replace_callback(
            '~<button ([^>]+) (data-product-id="(\d+)" data-replace-incart="([^"]+)")>.+</button>~i',
            function ($matches) {
                if (false === $this->isProductInQuote($matches[3])) {
                    return $matches[0];
                }

                $res = '<button ';
                $res .= preg_replace('~class="([^"]+)"~i', 'class="$1 incart"', $matches[1]);
                $res .= ' ' . $matches[2] . '>';
                $res .= '<span><span> ' . $matches[4] . '</span></span></button>';

                return $res;
            },
            $content
        );

        return $content;
    }

    /**
     * @param bool $fromQuote
     * @return $this
     */
    protected function fillProducts($fromQuote = true)
    {
        if (true === $fromQuote) {
            /** @var Mage_Sales_Model_Quote $quote */
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $this->_products[] = (int)$item->getProductId();
            }
        } else {
            try {
                if (null !== $qid = $this->getQuoteIdFromSession()) {
                    /** @var Mage_Core_Model_Resource $resource */
                    $resource = Mage::getSingleton('core/resource');
                    $adapter = $resource->getConnection('core_read');

                    $select = $adapter
                        ->select()
                        ->from('sales_flat_quote_item')
                        ->where('quote_id = ?', [$qid]);

                    foreach ($adapter->fetchAssoc($select) as $item) {
                        if (false === empty($item['product_id'])) {
                            $this->_products[] = (int)$item['product_id'];
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $this->_products = array_unique($this->_products);
        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function isProductInQuote($id)
    {
        return (true === in_array((int)$id, $this->_products));
    }

    /**
     * @return int|null
     */
    protected function getCustomerIdFromSession()
    {
        foreach ($_SESSION as $k => $v) {
            if (0 === strpos($k, 'customer')) {
                return (true === isset($v['id'])) ? (int)$v['id'] : null;
            }
        }

        return null;
    }

    /**
     * @return int|null
     */
    protected function getQuoteIdFromSession()
    {
        $id = null;
        foreach ($_SESSION as $k => $v) {
            if (0 === strpos($k, 'checkout')) {
                foreach ($v as $kk => $vv) {
                    if (0 === strpos($kk, 'quote_id_')) {
                        $id = (int)$vv;
                    }
                }
            }
        }

        return $id;
    }
}
