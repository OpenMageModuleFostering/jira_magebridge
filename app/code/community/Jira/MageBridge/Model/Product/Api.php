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

class Jira_MageBridge_Model_Product_Api extends Mage_Catalog_Model_Product_Api
{
    /**
     * Search for products 
     *
     * @param array $options
     * @return array
     */
    public function search($options = array())
    {
        // Construct the API-arguments
        $arguments = array(
            'store' => $options['store'],
            'website' => $options['website'],
            'count' => $options['search_limit'],
            'page' => 0,
            'visibility' => array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            ),
        );

        // Construct the search-filters
        $text = $options['text'];
        $arguments['filters'] = array(
            // @todo: Currently it's only possible to use AND. Is it also possible to have OR?
            //'name' => array( 'like' => "%$text%" ),
            'description' => array( 'like' => "%$text%" ),
        );

        // Return the list of products
        return Mage::getModel('magebridge/product_api')->items($arguments);
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $filters
     * @param string|int $store
     * @return array
     */
    public function items($arguments = null, $store = null)
    {
        // Set the visibility
        if(isset($arguments['visibility'])) {
            $visibility = $arguments['visibility'];
        } else {
            $visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            );
        }

        // Get the product-collection
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('visibility', $visibility)
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 1)
        ;

        // Set the website 
        if(!empty($arguments['website'])) {
            $collection->addWebsiteFilter($arguments['website']);
        }

        // Set the store
        if(!empty($arguments['store'])) {
            $collection->addStoreFilter($arguments['store']);
        }


        // Add the category
        if(isset($arguments['category_id']) && $arguments['category_id'] > 0) {
            $collection->addAttributeToFilter('category_ids', array('finset' => $arguments['category_id']));
        }

        // Add a filter
        // Example 1: array('title' => array('nlike' => array('%a', '%b')))
        // Example 2: array( array('attribute'=>'name', 'like'=>'P%')))
        // Example 3: array(
        //      array('attribute'=>'price','lt'=>'20'),
        //      array('attribute'=>'name','like'=>'Product C')
        //      ));
        if (isset($arguments['filters']) && is_array($arguments['filters'])) {
            $filters = $arguments['filters'];
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Invalid search filter', $e->getMessage());
            }
        }

        // Set ordering
        if(isset($arguments['ordering'])) {
            switch($arguments['ordering']) {
                case 'newest':
                    $collection->setOrder('created_at', 'desc');
                    break;
                case 'oldest':
                    $collection->setOrder('created_at', 'asc');
                    break;
                case 'popular':
                    $collection->setOrder('ordered_qty', 'desc');
                    break;
                case 'random':
                    $collection->getSelect()->order('rand()');
                    break;
            }
        }

        // Add a list limit
        if(isset($arguments['count'])) {
            $collection->setPageSize($arguments['count']);
        }

        // Add a page number
        if(isset($arguments['page']) && $arguments['page'] > 0) {
            $collection->setCurPage($arguments['page']);
        }

        $result = array();
        foreach ($collection as $product) {

            $result[] = array( // Basic product data
                'product_id'        => $product->getId(),
                'sku'               => $product->getSku(),
                'name'              => $product->getName(),
                'description'       => $product->getShortDescription(),
                'short_description' => $product->getShortDescription(),
                'label'             => htmlentities($product->getName()),
                'author'            => $product->getAuthor(),
                'url_key'           => $product->getUrlKey(),
                'url'               => $product->getProductUrl(false),
                'category_ids'      => $product->getCategoryIds(),
                'thumbnail'         => $product->getSmallImageUrl(),
                'price'             => Mage::app()->getStore()->formatPrice($product->getPrice()),
                'special_price'     => $product->getSpecialPrice(),
                'special_from_date' => $product->getSpecialFromDate(),
                'special_to_date'   => $product->getSpecialToDate(),
                'is_active'         => 1,
            );
        }

        return $result;
    }
}
