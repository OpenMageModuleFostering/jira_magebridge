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

class Jira_MageBridge_Block_License extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/license.phtml');
    }

    /*
     * Helper method to get data from the Magento configuration
     */
    public function getLicenseKey()
    {
        return Mage::helper('magebridge')->getLicenseKey();
    }

    /*
     * Helper to return the header of this page
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - '.$this->__($title);
    }

    /*
     * Helper to return the menu
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    public function getSaveUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('magebridge/index/save');
    }
}
