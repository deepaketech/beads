<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
require_once 'AjaxloginController.php';
class Amasty_Ajaxlogin_GoogleController extends Amasty_Ajaxlogin_AjaxloginController
{
    public function indexAction() {
        if (Mage::app()->getRequest()->getParam('code')) {
            $helper = Mage::helper('amajaxlogin');
            $params = array(
                'client_id'     => Mage::helper('amajaxlogin/google')->getAppId(),
                'redirect_uri'  => Mage::helper('amajaxlogin/google')->getUrl(),
                'client_secret' => Mage::helper('amajaxlogin/google')->getSecretId(),
                'grant_type'    => 'authorization_code',
                'code'          => Mage::app()->getRequest()->getParam('code')
            );

            $url = 'https://accounts.google.com/o/oauth2/token';
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);

            try {
                $tokenInfo = Mage::helper('core')->jsonDecode($result, true);
            } catch (\Exception $ex) {
                $helper->printError($tokenInfo);
            }

            if (isset($tokenInfo['access_token'])) {
                $token = $params['access_token'] = $tokenInfo['access_token'];

                $userInfo = Mage::helper('amajaxlogin')->getContentByCurl(
                    'https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query($params))
                );
                try {
                    $userInfo = Mage::helper('core')->jsonDecode($userInfo, true);
                } catch (\Exception $ex) {
                    $helper->printError($userInfo);
                }

                if (isset($userInfo['given_name'])) {
                    $userInfo['first_name'] = $userInfo['given_name'];
                }
                if (isset($userInfo['family_name'])) {
                    $userInfo['last_name'] = $userInfo['family_name'];
                }

                if (isset($userInfo['id'])) {
                    Mage::getSingleton('core/session')->setData('amajaxlogin_google_data', $userInfo);
                    Mage::getSingleton('core/session')->setData('amajaxlogin_google_token', $token);
                    Mage::helper('ambase/utils')->_echo($this->__("Window will close automatically. Now you can login using your Google account."));
                    Mage::helper('ambase/utils')->_echo("<script>setTimeout(function() {
                            window.close();
                        }, 500);</script>");
                } else {
                    $helper->printError('User id is not presented');
                }
            } else {
                $helper->printError('Access token is not presented');
            }
        }
    }
    
    public function loginAction() {
         $data = Mage::getSingleton('core/session')->getData('amajaxlogin_google_data');
         $oauth_token = Mage::getSingleton('core/session')->getData('amajaxlogin_google_token');
         if($data && $oauth_token)
            $this->_login($data, $oauth_token, 'g', $this->__('Google'));
     }
    
     public function iframeAction() {
         $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_google', 'amajaxlogin_google')
                             ->setTemplate('amasty/amajaxlogin/social/google.phtml');
         Mage::helper('ambase/utils')->_echo($block->toHtml());
     }
    
    public function replaceJs($result)
    {
         $arrScript = array();
         $result['script'] = '';               
         preg_match_all("@<script type=\"text/javascript\">(.*?)</script>@s",  $result['message'], $arrScript);
         $result['message'] = preg_replace("@<script type=\"text/javascript\">(.*?)</script>@s",  '', $result['message']);
         foreach($arrScript[1] as $script){ 
             $result['script'] .= $script;                 
         }
         $result['script'] =  preg_replace("@var @s",  '', $result['script']); 
         return "<plaintext>" . Zend_Json::encode($result);
    } 
}
