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

class Jira_MageBridge_Model_Storeviews_Api extends Mage_Api_Model_Resource_Abstract
{
    public function items()
    {
        $views = Mage::getModel('core/store')->getCollection();

        $res = array();
        foreach ($views as $item) {
            $data = array();
            $data['value'] = $item->getData('code');
            $data['label'] = $item->getData('name');
            $res[] = $data;
        }
        return $res;
    }

    public function hierarchy()
    {
        $groups = Mage::getModel('core/store_group')->getCollection();
        $views = Mage::getModel('core/store')->getCollection();

        $res = array();
        foreach ($groups as $item) {
            $data['value'] = $item->getData('group_id');
            $data['label'] = $item->getData('name');
            $data['childs'] = array();

            foreach($views as $view) {
                $child = array(
                    'value' => $view->getData('code'),
                    'label' => $view->getData('name'),
                );
                $data['childs'][] = $child;
            }
            $res[] = $data;
        }
        return $res;
    }
}
