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

class Jira_MageBridge_Model_Websites_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items()
    {
        $websites = Mage::getModel('core/website')->getCollection();

        $res = array();
        foreach ($websites as $item) {
            $data['value'] = $item->getData('website_id');
            $data['label'] = $item->getData('name');

            $data['id'] = $item->getData('website_id');
            $data['name'] = $item->getData('name');
            $data['code'] = $item->getData('code');
            $res[] = $data;
        }

        return $res;
    }
}
