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

class Jira_MageBridge_Model_Url extends Mage_Core_Model_Abstract
{
    /**
     * Data
     *
     * @var mixed
     */
    var $_data = null;

    public function getData($type = 'product', $id = null)
    {
        static $urls = array();
        if(empty($urls[$type])) {

            $magebridge = Mage::getSingleton('magebridge/core');
            $urls[$type] = array();

            switch($type) {

                case 'category':
                    $categories = Mage::getModel('catalog/category')->getTreeModel();
                    $helper = Mage::helper('catalog/category');
                    $categories = $helper->getStoreCategories('name', true, false);
                    foreach($categories as $category) {
                        $urls[$type][] = array( 'id' => $category->getId(), 'url' => $magebridge->parse($category->getUrl()));
                    }
                    break;

                case 'product':
                default:
                    $products = Mage::getModel('catalog/product')->getCollection();
                    foreach($products as $index => $product) {
                        $urls[$type][] = array( 'id' => $product->getId(), 'url' => $magebridge->parse($product->getProductUrl()));
                    }
                    break;
            }
        }

        if($id > 0) {
            return $urls[$type][$id];
        } else {
            return (array)$urls[$type];
        }
    }
}
