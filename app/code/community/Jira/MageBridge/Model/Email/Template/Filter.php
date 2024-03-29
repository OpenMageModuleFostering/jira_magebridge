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

/*
 * Override of the default class Mage_Core_Model_Email_Template_Filter 
 */
class Jira_MageBridge_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * Use absolute links flag
     *
     * @var bool
     */
    protected $_useAbsoluteLinks = true;

    /*
     * Override the default constructor to make sure the URLs are SEF-ed in emails 
     */
    public function storeDirective($construction)
    {
        // Get the bridge URLs
        $bridge = Mage::getSingleton('magebridge/core');
        $joomla_url = $bridge->getMageBridgeUrl();
        $joomla_sef_url = $bridge->getMageBridgeSefUrl();

        // Remove the .html suffix from the URL
        if(preg_match('/\.html$/', $joomla_sef_url)) {
            $url_suffix = true;
            $joomla_sef_url = preg_replace( '/\.html$/', '', $joomla_sef_url );
        } else {
            $url_suffix = false;
        }

        // Call the parent function
        $url = parent::storeDirective($construction);
        $store_code = Mage::app()->getStore(Mage::getDesign()->getStore())->getCode();
        $url = str_replace($joomla_url, $joomla_sef_url, $url);
        $url = preg_replace( '/___store='.$store_code.'/', '', $url );
        $url = preg_replace( '/\?$/', '', $url );
        $url = preg_replace( '/\&$/', '', $url );

        // Return the URL
        return $url;
    }
}
