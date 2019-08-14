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

class Jira_MageBridge_Model_Search extends Jira_MageBridge_Model_Block
{
    public static function getSearch($arguments)
    {
        $query = Mage::getModel('catalogsearch/query')
            ->loadByQuery($arguments)
            ->setQueryText($arguments);
        
            foreach($items as $item) {
                $messages[] = array(
                    'type' => $item->getType(), 
                    'message' => $item->getCode()
                );
            }

            return $messages;

        return array();
    }
}
