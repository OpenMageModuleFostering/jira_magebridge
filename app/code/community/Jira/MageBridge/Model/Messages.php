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

class Jira_MageBridge_Model_Messages extends Jira_MageBridge_Model_Block
{
    public static function getMessages()
    {
        $classes = array(
            'core/session',
            'customer/session',
            'checkout/session',
            'catalog/session',
            'tag/session',
        );

        $messages = array();
        try {
            foreach($classes as $class) {
                $messages = array_merge($messages, Mage::getModel('magebridge/messages')->_getMessages($class));
            }

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load messages: '.$e->getMessage());
        }

        return $messages;
    }

    public static function _getMessages($class)
    {
        try {
            $items = Mage::getSingleton($class)->getMessages()->getItems();
            $messages = array();

            foreach($items as $item) {
                $messages[] = array(
                    'type' => $item->getType(), 
                    'message' => $item->getCode()
                );
            }

            return $messages;

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get messages: '.$e->getMessage());

        }
        return array();
    }
}
