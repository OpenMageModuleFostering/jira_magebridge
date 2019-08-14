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

class Jira_MageBridge_Model_Dispatcher extends Mage_Core_Model_Abstract
{
    /*
     * Method to fire a Joomla! event sent through the bridge
     */
    public function getResult($name, $arguments)
    {
        if(in_array($event, $this->getEvents())) {

            $event = 'joomla'.ucfirst($name);
            return Mage::dispatchEvent($event, $arguments);

        }
        return false;
    }

    public function getEvents()
    {
        return array(
            'onAuthenticate',
            'onPrepareContent',
            'onAfterDisplayTitle',
            'onBeforeDisplayContent',
            'onAfterDisplayContent',
            'onBeforeContentSave',
            'onAfterContentSave',
            'onSearch',
            'onSearchAreas',
            'onAfterInitialise',
            'onAfterRender',
            'onLoginFailure',
            'onBeforeStoreUser',
            'onAfterStoreUser',
            'onBeforeDeleteUser',
            'onAfterDeleteUser',
            'onLoginUser',
            'onLogoutUser',
        );
    }
}
