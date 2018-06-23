<?php

class MageAssist_FpcInCart_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function onHttpResponseSendBefore(Varien_Event_Observer $observer)
    {
        $this->addActionNameToResponse('onHttpResponseSendBefore');

        $isAJAX = $this->getRequest()->getParam('is_ajax', false);
        $isAJAX = $isAJAX && $this->getRequest()->isXmlHttpRequest();

        $response = $this->getResponse();
        /** @var MageAssist_FpcInCart_Model_Replace $model */
        $model = Mage::getModel('ma_fpcincart/replace');

        if (!$isAJAX) {
            $response->setBody(
                $model->replaceContent($response->getBody(), $this->getFullActionName())
            );
        } else {
            $allowed = [
                'catalog_category_view',
                'catalog_product_view',
                'bssreorderproduct_list_index',
                'amshopby_index_index',
                'catalogsearch_result_index',
            ];
            if (true === in_array($this->getFullActionName(), $allowed)) {
                try {
                    $json = json_decode($response->getBody(), true);
                    if (true === isset($json['page'])) {
                        $json['page'] = $model->replaceContent($json['page'], $this->getFullActionName());
                        $response->setBody(json_encode($json));
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleActionPostDispatch(Varien_Event_Observer $observer)
    {
        $this->addActionNameToResponse('handleActionPostDispatch');

        $isAJAX = $this->getRequest()->getParam('is_ajax', false);
        $isAJAX = $isAJAX && $this->getRequest()->isXmlHttpRequest();

        $response = $this->getResponse();
        /** @var MageAssist_FpcInCart_Model_Replace $model */
        $model = Mage::getModel('ma_fpcincart/replace');

        $canPreserve = Mage::registry('amfpc_preserve');
        $allowed = [
            'catalog_category_view',
            'catalog_product_view',
            'bssreorderproduct_list_index',
            'amshopby_index_index',
            'catalogsearch_result_index',
        ];

        if (!$isAJAX) {
            if (true === in_array($this->getFullActionName(), $allowed)
                && !$canPreserve
            ) {
                $response->setBody(
                    $model->replaceContent($response->getBody(), $this->getFullActionName())
                );
            }
        } else {
            if (true === in_array($this->getFullActionName(), $allowed)
                && !$canPreserve
            ) {
                try {
                    $json = json_decode($response->getBody(), true);
                    if (true === isset($json['page'])) {
                        $json['page'] = $model->replaceContent($json['page'], $this->getFullActionName());
                        $response->setBody(json_encode($json));
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * @return Mage_Core_Controller_Response_Http
     */
    protected function getResponse()
    {
        return Mage::app()->getResponse();
    }

    /**
     * @return string
     */
    protected function getFullActionName()
    {
        $request = $this->getRequest();
        return "{$request->getRequestedRouteName()}_{$request->getRequestedControllerName()}_{$request->getRequestedActionName()}";
    }

    /**
     * @param string|null $observer
     * @return void
     */
    protected function addActionNameToResponse($observer = null)
    {
        return ;

        $action = $this->getFullActionName();
        if (null !== $observer) {
            $action = "[{$observer} | {$action}]";
        }
        $this->getResponse()->setHeader('Debug-Action-Name', $action);
    }
}
