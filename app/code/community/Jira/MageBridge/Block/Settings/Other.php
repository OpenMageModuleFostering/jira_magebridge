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

class Jira_MageBridge_Block_Settings_Other extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/settings/other.phtml');
    }

    /*
     * Helper method to get the current value of the bridge-all setting
     */
    public function getBridgeAll()
    {
        return Mage::getStoreConfig('magebridge/settings/bridge_all');
    }
}
