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

class Jira_MageBridge_Block_Settings_Joomla extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/settings/joomla.phtml');
    }

    /*
     * Helper method to get the current value of the Joomla! XML-RPC host
     */
    public function getXmlrpcUrl()
    {
        return Mage::getStoreConfig('magebridge/settings/xmlrpc_url');
    }

    /*
     * Helper method to get the currently configured Joomla! API user
     */
    public function getApiUser()
    {
        return Mage::getStoreConfig('magebridge/settings/api_user');
    }

    /*
     * Helper method to get the currently configured Joomla! API key
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig('magebridge/settings/api_key');
    }
}
