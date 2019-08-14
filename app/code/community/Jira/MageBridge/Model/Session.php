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

/*
 * Class for importing and exporting data out of a session
 */
class Jira_MageBridge_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('magebridge');
    }

    public function addEvent($group, $event, $arguments)
    {
        $events = $this->getData('events');
        if(empty($events)) $events = array();

        $events[] = array(
            'type' => 'magento',
            'group' => $group,
            'event' => $event,
            'arguments' => $arguments,
        );
        $this->setData('events', $events);
    }

    public function getEvents()
    {
        return $this->getData('events');
    }

    public function cleanEvents()
    {
        $this->setData('events');
    }
}
