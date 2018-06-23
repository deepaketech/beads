<?php /**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */

require_once 'AjaxloginController.php';

class Amasty_Ajaxlogin_TwitterController extends Amasty_Ajaxlogin_AjaxloginController
{
    const URL_REQUEST_TOKEN   = 'https://api.twitter.com/oauth/request_token';
    const URL_AUTHORIZE       = 'https://api.twitter.com/oauth/authorize';
    const URL_ACCESS_TOKEN    = 'https://api.twitter.com/oauth/access_token';
    const URL_ACCOUNT_DATA    = 'https://api.twitter.com/1.1/users/show.json';
    const URL_EMAIL           = 'https://api.twitter.com/1.1/account/verify_credentials.json';

    protected $_oauthNonce;
    protected $_oauthTimestamp;

    public function indexAction()
    {
        if (Mage::app()->getRequest()->getParam('oauth_token')) {
            $this->_oauthNonce = md5(uniqid(rand(), true));
            $this->_oauthTimestamp = time();

            list($newOauthToken, $newOauthTokenSecret) = $this->getTokensFromTwitter();

            $signature = $this->getSignature($newOauthToken, $newOauthTokenSecret);

            $content = $this->getUserInfo($newOauthToken, $signature);

            if (isset($content->errors)) {
                Mage::log($content, null, "authorization.log");
                $this->showCartPopup($content->errors['0']->message, '', '', 1);
                return;
            }

            Mage::getSingleton('core/session')->setData('amajaxlogin_twitter_data', $this->objectToArray($content));
            Mage::getSingleton('core/session')->setData('amajaxlogin_twitter_token', $newOauthToken);
            Mage::helper('ambase/utils')->_echo($this->__("Window will close automatically. Now you can login using your Twitter account."));
            Mage::helper('ambase/utils')->_echo("<script>setTimeout(function() {
                    window.close();
                }, 500);</script>"
            );
        }
    }

    public function getSignature($oAuthToken, $oAuthTokenSecret)
    {
        $oauthBaseText = array(
            http_build_query(array('include_email' => 'true')),
            "oauth_consumer_key=" . Mage::helper('amajaxlogin/twitter')->getAppId(),
            "oauth_nonce=" . $this->_oauthNonce,
            "oauth_signature_method=HMAC-SHA1",
            "oauth_timestamp=" . $this->_oauthTimestamp,
            "oauth_token=" . $oAuthToken,
            "oauth_version=1.0"
        );
        $oauthBaseText = 'GET&' . urlencode(self::URL_EMAIL) . '&' . urlencode(implode('&', $oauthBaseText));

        $parts = array(
            Mage::helper('amajaxlogin/twitter')->getSecretId(),
            null !== $oAuthToken ? $oAuthTokenSecret : ""
        );

        $key = implode('&', $parts);

        return base64_encode(hash_hmac("sha1", $oauthBaseText, $key, true));
    }

    public function getUserInfo($oAuthToken, $signature)
    {
        $authorization = array(
            ' oauth_version="1.0"',
            ' oauth_nonce="' . $this->_oauthNonce . '"',
            ' oauth_timestamp="' . $this->_oauthTimestamp . '"',
            ' oauth_consumer_key="' . Mage::helper('amajaxlogin/twitter')->getAppId() . '"',
            ' oauth_token="' . urlencode($oAuthToken) . '"',
            ' oauth_signature_method="HMAC-SHA1"',
            ' oauth_signature="' . urlencode($signature) . '"'
        );
        $authorization = 'Authorization: OAuth'. implode(',', $authorization);

        $getEmailUrl = self::URL_EMAIL;
        $getEmailUrl .= '?' . http_build_query(array('include_email' => 'true'));
        $responseWithEmail = $this->getContentByCurl($getEmailUrl, $authorization);
        $partsOfResponse = explode("\r\n\r\n", $responseWithEmail);
        $responseBody = array_pop($partsOfResponse);
        $content = json_decode($responseBody);

        return $content;
    }

    public function getTokensFromTwitter()
    {
        $oauthToken = Mage::app()->getRequest()->getParam('oauth_token');
        $oauthVerifier = Mage::app()->getRequest()->getParam('oauth_verifier');

        $oauthTokenSecret = Mage::getSingleton('core/session')->getData('oauth_token_secret');

        $oauthBaseText = array(
            "GET",
            self::URL_ACCESS_TOKEN,
            "oauth_consumer_key=" . Mage::helper('amajaxlogin/twitter')->getAppId(),
            "oauth_nonce=" . $this->_oauthNonce,
            "oauth_signature_method=HMAC-SHA1",
            "oauth_token=" . $oauthToken,
            "oauth_timestamp=" . $this->_oauthTimestamp,
            "oauth_verifier=" . $oauthVerifier,
            "oauth_version=1.0"
        );
        $oauthBaseText = implode('&', $oauthBaseText);

        $key = Mage::helper('amajaxlogin/twitter')->getSecretId() . "&" . $oauthTokenSecret;

        $oauthSignature = base64_encode(hash_hmac("sha1", $oauthBaseText, $key, true));

        $url = array(
            self::URL_ACCESS_TOKEN . '?' . 'oauth_nonce=' . $this->_oauthNonce,
            "oauth_signature_method=HMAC-SHA1",
            "oauth_timestamp=" . $this->_oauthTimestamp,
            "oauth_consumer_key=" . Mage::helper('amajaxlogin/twitter')->getAppId(),
            "oauth_token=" . $oauthToken,
            "oauth_verifier=" . $oauthVerifier,
            "oauth_signature=" . $oauthSignature,
            "oauth_version=1.0"
        );
        $url = implode('&', $url);

        $response = Mage::helper('amajaxlogin')->getContentByCurl($url);
        parse_str($response, $result);

        if (!$result['oauth_token'] || !$result['oauth_token_secret']) {
            $this->showCartPopup($this->__('Reply from twitter does not contain token.'), '', '', 1);
            return;
        }

        return array($result['oauth_token'], $result['oauth_token_secret']);
    }

    public function getContentByCurl($url, $authorization)
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 1,
            CURLOPT_HTTPHEADER => array('Accept: application/json', $authorization, 'Expect:'),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => 1,
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            Mage::log(curl_error($ch), null, "authorization.log");
        }
        curl_close($ch);
        return $response;
    }
    
     public function iframeAction()
     {
         $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_twitter', 'amajaxlogin_twitter')
                             ->setTemplate('amasty/amajaxlogin/social/twitter.phtml');
         Mage::helper('ambase/utils')->_echo($block->toHtml());
     }
     
     public function loginAction()
     {
         $data = Mage::getSingleton('core/session')->getData('amajaxlogin_twitter_data');
         $oauthToken = Mage::getSingleton('core/session')->getData('amajaxlogin_twitter_token');
         if ($data && $oauthToken) {
             $this->_login($data, $oauthToken, 'tw', $this->__('Twitter'));
         }
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
    
    public function objectToArray($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
 
        if (is_array($d)) {
            return $d;
        } else {
            return null;
        }
    }
}
