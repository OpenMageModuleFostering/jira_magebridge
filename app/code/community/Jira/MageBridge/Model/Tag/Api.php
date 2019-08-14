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

class Jira_MageBridge_Model_Tag_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items($tags = array())
    {
        if(empty($tags) || !is_array($tags)) {
            return false;
        }

        $result = array();

        foreach($tags as $tag) {

            $tagModel = Mage::getModel('tag/tag')->loadByName((string)$tag);
            $products = $tagModel->getEntityCollection()->addTagFilter($tagModel->getTagId());

            foreach($products as $product) {
                $p = array();
                $p['name'] = $product->getName();
                $p['url'] = $product->getProductUrl(false);
                $result[$product->getId()] = $p;
            }
        }

        return $result;
    }
}
