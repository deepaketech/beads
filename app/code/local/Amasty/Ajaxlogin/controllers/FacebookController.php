<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */

require_once 'AjaxloginController.php';

class Amasty_Ajaxlogin_FacebookController extends Amasty_Ajaxlogin_AjaxloginController
{
    private $_params = array();

    public function indexAction()
    {
        if (Mage::app()->getRequest()->getParam('code')) {
            $this->_params = array(
                'client_id'     => Mage::helper('amajaxlogin/facebook')->getAppId(),
                'redirect_uri'  => Mage::helper('amajaxlogin/facebook')->getUrl(),
                'client_secret' => Mage::helper('amajaxlogin/facebook')->getSecretId(),
                'code'          => Mage::app()->getRequest()->getParam('code')
            );

            $url = 'https://graph.facebook.com/oauth/access_token';
            $tokenInfo = null;

            $tokenInfo = Mage::helper('amajaxlogin')
                ->getContentByCurl($url . '?' . http_build_query($this->_params));

            try {
                $tokenInfo = Mage::helper('core')->jsonDecode($tokenInfo);
            } catch (\Exception $ex) {
                Mage::helper('ambase/utils')->_echo($tokenInfo);
                Mage::helper('ambase/utils')->_exit();
            }

            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
                $token = $tokenInfo['access_token'];

                $this->_params = array('access_token' => $token);
                $url = 'https://graph.facebook.com/me';
                $params = urldecode(http_build_query($this->_params)) . '&fields=id,name,first_name,last_name,email';

                $userInfo = Mage::helper('amajaxlogin')->getContentByCurl($url . '?' . $params);

                try {
                    $userInfo = Mage::helper('core')->jsonDecode($userInfo);
                } catch (\Exception $ex) {
                    Mage::helper('ambase/utils')->_echo($userInfo);
                    Mage::helper('ambase/utils')->_exit();
                }

                if (isset($userInfo['id'])) {
                    $this->_login($userInfo, $token, 'fb', $this->__('Facebook'));
                }

            } else {
                Mage::helper('ambase/utils')->_echo($this->__('There were not access_token in facebook answer. '));
                Mage::helper('ambase/utils')->_echo(Mage::helper('core')->jsonDecode($tokenInfo));
                Mage::helper('ambase/utils')->_exit();
            }
        }
    }

    public function iframeAction()
    {
        $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_facebook', 'amajaxlogin_facebook')
            ->setTemplate('amasty/amajaxlogin/social/facebook.phtml');
        Mage::helper('ambase/utils')->_echo($block->toHtml());
    }

    public function replaceJs($result)
    {
        $arrScript = array();
        $result['script'] = '';
        preg_match_all("@<script type=\"text/javascript\">(.*?)</script>@s", $result['message'], $arrScript);
        $result['message'] = preg_replace("@<script type=\"text/javascript\">(.*?)</script>@s", '', $result['message']);
        foreach ($arrScript[1] as $script) {
            $result['script'] .= $script;
        }
        $result['script'] =  preg_replace("@var @s", '', $result['script']);
        return "<plaintext>" . Zend_Json::encode($result);
    }
}
