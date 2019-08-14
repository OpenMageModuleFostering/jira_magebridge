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

class Jira_MageBridge_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function enabled()
    {
        return (bool)Mage::getStoreConfig('magebridge/settings/active');
    }

    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/settings/license_key');
    }

    public function getXmlrpcUrl()
    {
        $value = Mage::getSingleton('magebridge/core')->getMetaData('xmlrpc_url');
        if(empty($value)) {
            $value = Mage::getStoreConfig('magebridge/settings/xmlrpc_url');
        } 
        return $value;
    }

    public function getApiUser()
    {
        $value = Mage::getSingleton('magebridge/core')->getMetaData('api_user');
        if(empty($value)) {
            $value = Mage::getStoreConfig('magebridge/settings/api_user');
        } 
        return $value;
    }

    public function getApiKey()
    {
        $value = Mage::getSingleton('magebridge/core')->getMetaData('api_key');
        if(empty($value)) {
            $value = Mage::getStoreConfig('magebridge/settings/api_key');
        } 
        return $value;
    }

    public function debug($message, $variable = null)
    {
        if(!empty($variable)) {
            $message .= ': '.var_export($variable, true);
        }
        $message .= "\n";
        file_put_contents('/tmp/magebridge.debug', $message, FILE_APPEND);
    }
}
