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

class Jira_MageBridge_Model_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    public static function getBreadcrumbs()
    {
        try {
            $controller = Mage::getSingleton('magebridge/core')->getController();
            $controller->getResponse()->clearBody();

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        try {
            $block = $controller->getAction()->getLayout()->getBlock('breadcrumbs');

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get breadcrumbs: '.$e->getMessage());
            return false;
        }

        try {
            if(!empty($block)) {
                $block->toHtml();
                return $block->getCrumbs();
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to set block: '.$e->getMessage());
            return false;
        }
    }

    public function getCrumbs()
    {
        return $this->_crumbs;
    }
}
