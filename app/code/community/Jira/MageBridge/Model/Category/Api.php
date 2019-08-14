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

class Jira_MageBridge_Model_Category_Api extends Mage_Catalog_Model_Api_Resource
{
    public function tree($parentId = null, $store = null)
    {
        // Get a tree of all categories
        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();

        if (is_null($parentId) && !is_null($store)) {
            $parentId = Mage::app()->getStore($this->_getStoreId($store))->getRootCategoryId();
        } elseif (is_null($parentId)) {
            $parentId = 1;
        }

        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();

        $root = $tree->getNodeById($parentId);
        if($root && $root->getId() == 1) {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($this->_getStoreId($store))
            ->addUrlRewriteToResult()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('is_active')
        ;

        $tree->addCollectionData($collection, true);
        return $this->_nodeToArray($root);
    }

    protected function _nodeToArray(Varien_Data_Tree_Node $node)
    {
        $result = array();
        $result['category_id'] = $node->getId();
        $result['parent_id']   = $node->getParentId();
        $result['name']        = $node->getName();
        $result['is_active']   = $node->getIsActive();
        $result['is_anchor']   = $node->getIsAnchor();
        $result['url_key']     = $node->getUrlKey();
        $result['url']         = $node->getRequestPath();
        $result['position']    = $node->getPosition();
        $result['level']       = $node->getLevel();
        $result['children']    = array();

        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_nodeToArray($child);
        }

        return $result;
    }
}
