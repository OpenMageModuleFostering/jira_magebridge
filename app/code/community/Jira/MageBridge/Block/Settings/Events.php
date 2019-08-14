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

class Jira_MageBridge_Block_Settings_Events extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/settings/events.phtml');
    }

    /*
     * Helper method to get list of all the forwarded events and their current status
     */
    public function getEvents()
    {
        $events = Mage::getModel('magebridge/listener')->getEvents();
        $event_list = array();
        foreach($events as $event) {
            $event_list[] = array(
                'name' => $event[0],
                'value' => (int)Mage::getStoreConfig('magebridge/settings/event_forwarding/'.$event[0]),
            );
        }
        return $event_list;
    }
}
