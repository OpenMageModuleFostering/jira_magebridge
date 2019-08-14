<?php
/**
 * Magento Bridge
 *
 * @author Yireo
 * @package Magento Bridge
 * @copyright Copyright 2009
 * @license Yireo EULA (www.yireo.com)
 * @link http://www.yireo.com
 */

class Jira_MageBridge_Model_Encryption extends Mage_Core_Model_Abstract
{
    /*
     * Get some kind of string that is specific for this host
     */
    public function getKey($string)
    {
        return md5(Mage::getSingleton('magebridge/core')->getLicenseKey().$string);
    }

    /*
     * Encrypt data for security
     */
    public function encrypt($data)
    {
        $data = trim($data);
        $random = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
        $key = $this->getKey($random);

        $iv = substr($key, 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));

        $encrypted = mcrypt_cfb (MCRYPT_CAST_256, $key, $data, MCRYPT_ENCRYPT, $iv);
        $encoded = base64_encode($encrypted);
        return $encoded.'|'.$random;
    }

    /*
     * Decrypt data after encryption
     */
    public function decrypt($data)
    {
        if(empty($data)) {
            return null;
        }

        // This is a serious bug: Base64-encoding can include plus-signs, but JSON thinks these are URL-encoded spaces. 
        // We have to convert them back manually. Ouch! Another solution would be to migrate from JSON to another transport mechanism. Again ouch!
        $data = str_replace(' ', '+', $data);

        $array = explode( '|', $data);
        if(isset($array[0]) && isset($array[1])) {
            $encrypted = base64_decode($array[0], true);
            $key = $this->getKey($array[1]);
            $iv = substr($key, 0,mcrypt_get_iv_size (MCRYPT_CAST_256,MCRYPT_MODE_CFB));
        } else {
            return null;
        }


        try {
            $decrypted = mcrypt_cfb (MCRYPT_CAST_256, $key, $encrypted, MCRYPT_DECRYPT, $iv);
            $decrypted = trim($decrypted);
            return $decrypted;

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
            return null;
        }
    }
}
