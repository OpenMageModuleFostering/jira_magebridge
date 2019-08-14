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
 * Class for outputting HTML-blocks from the Magento theme
 */
class Jira_MageBridge_Model_Block extends Mage_Core_Model_Abstract
{
    /*
     * Get the block
     */
    public function getBlock($block_name)
    {
        if(empty($block_name)) {
            Mage::getSingleton('magebridge/debug')->warning('Empty block-name');
            return null;
        }

        Mage::getSingleton('magebridge/debug')->notice('Building block "'.$block_name.'"');

        // Initialize the controller
        try {
            $controller = Mage::getSingleton('magebridge/core')->getController();
            $controller->getResponse()->clearBody();

            // @todo: Remove the messages from the content-block
            // @todo: Add a parameter to include the messages-block or not
            //$controller->getAction()->getLayout()->removeBlock('global_messages');
            //$controller->getAction()->getLayout()->removeBlock('messages');

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        // Initialize the block
        try {
            $block = $controller->getAction()->getLayout()->getBlock($block_name);

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get block: '.$block_name.': '.$e->getMessage());
            return false;
        }

        if(empty($block)) {
            Mage::getSingleton('magebridge/debug')->warning('Empty block-name');
            return null;
        }

        return $block;
    }

    /*
     * Output
     */
    public function getOutput($block_name, $arguments = array())
    {
        // Choose between regular blocks and CMS-blocks
        if(isset($arguments['type']) && $arguments['type'] == 'cms') {
            $response = $this->getCmsOutput($block_name, $arguments);
        } else {
            $response = $this->getBlockOutput($block_name, $arguments);
        }

        // Prepare the response for the bridge
        if(!empty($response)) {
            $response = base64_encode(gzcompress($response));
        }
        return $response;
    }

    /*
     * CMS-block output 
     */
    public function getCmsOutput($block_name, $arguments = array())
    {
        // Get the CMS-block
        $block = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($block_name);

        if($block->getIsActive()) {

            $response = $block->getContent();
            $response = Mage::getModel('core/email_template_filter')->filter($response);
            return $response;
        }

        return null;
    }

    /*
     * Regular block output 
     */
    public function getBlockOutput($block_name, $arguments = array())
    {
        // Get the block-object
        $block = $this->getBlock($block_name);
        if(empty($block)) {
            return null;
        }

        // Get the HTML of the block-object
        try {
            return $block->toHtml();

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get html from block '.$block_name.': '.$e->getMessage());
        }
            
        return null;
    }

    /*
     * Method to get extra information on this block
     */
    public function getMeta($block_name)
    {
        $block = $this->getBlock($block_name);
        if(empty($block)) {
            return null;
        }

        return array(
            'cache_key' => $block->getCacheKey(),
            'has_cache_key' => (int)$block->hasData('cache_key'),
            'cache_lifetime' => (int)$block->getCacheLifetime(),
            'cache_tags' => $block->getCacheTags(),
            'allow_caching' => (int)Mage::app()->useCache('block_html'),
        );
    }
}
