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

class Jira_MageBridge_Model_Order_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of orders with basic info 
     *
     * @param array $filters
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->setOrder('created_at', 'desc')
            ->setPageSize(20)
            ->load()
        ;

        /*
         * @todo: This does not work, but is still needed: $filter = array( array('title' => array('nlike' => array('%a', '%b'))));
         */
        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();
        foreach ($collection as $order) {
            $order->base_grand_total_formatted = $order->formatPrice($order->getBaseGrandTotal());
            $result[] = $order->debug();
        }

        return $result;
    }
}
