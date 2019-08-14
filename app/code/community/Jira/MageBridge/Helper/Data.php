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
}
