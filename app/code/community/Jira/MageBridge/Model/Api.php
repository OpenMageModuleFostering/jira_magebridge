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

class Jira_MageBridge_Model_Api extends Mage_Core_Model_Abstract
{
    public function getResult($resource, $arguments = null)
    {
        if(empty($resource)) {
            Mage::getSingleton('magebridge/debug')->warning('Empty API resource');
            return null;
        }

        try {
            // Parse the resource
            $resourceArray = explode( '.', $resource );
            $class = str_replace('_', '/', $resourceArray[0]).'_api';
            $method = $resourceArray[1];

            Mage::getSingleton('magebridge/debug')->notice('Calling API '.$class.'::'.$method);
            Mage::getSingleton('magebridge/debug')->trace('API arguments', $arguments);

            try {
                $model = Mage::getModel($class);
            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Failed to instantiate API-class '.$class.': '.$e->getMessage());
                return false;
            }

            if(empty($model)) {
                Mage::getSingleton('magebridge/debug')->notice('API class returns empty object');
                return false;

            } elseif(method_exists($model, $method)) {
                return call_user_func(array($model, $method), $arguments);

            } elseif($method == 'list' && method_exists($model, 'items')) {
                return $model->items($arguments);

            } else {
                Mage::getSingleton('magebridge/debug')->notice('API class has no method '.$method);
                return false;
            }

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to call API: '.$resource.': '.$e->getMessage());
            return false;
        }
    }
}
