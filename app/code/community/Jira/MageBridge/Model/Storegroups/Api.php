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

class Jira_MageBridge_Model_Storegroups_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items()
    {
        $groups = Mage::getModel('core/store_group')->getCollection();

        $res = array();
        foreach ($groups as $item) {
            $data['value'] = $item->getData('group_id');
            $data['label'] = $item->getData('name');
            $res[] = $data;
        }
        return $res;
    }
}
