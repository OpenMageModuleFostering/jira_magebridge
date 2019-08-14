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

class Jira_MageBridge_Model_Client extends Mage_Core_Model_Abstract
{
    /*
     *
     */
    private $calls = array();

    /*
     * Method to add a call
     */
    public function addCall($method, $arguments)
    {
        $this->calls[] = array($method, $arguments);
        return;
    }

    /*
     * Method to add all current debugging-data to a multicall
     */
    public function addDebug()
    {
        foreach(Mage::getSingleton('magebridge/debug')->getData() as $log) {
            foreach(array('type', 'message', 'section', 'time') as $index) {
                if(!isset($log[$index])) $log[$index] = '';
            }
            $this->addCall( 'magebridge.log', array($log['type'], $log['message'], $log['section'], $log['time']) );
        }
        Mage::getSingleton('magebridge/debug')->clean();
    }

    /*
     * Method to call multiple XML-RPC methods
     * @todo: Truely implement XML-RPC multi-call
     */
    public function multicall()
    {
        foreach($this->calls as $call) {
            $this->call($call[0], $call[1]);
        }
    }

    /*
     * Method to call a XML-RPC method
     */
    public function call($method, $params)
    {
        // Collect all the values from the bridge
        $xmlrpc_url = Mage::helper('magebridge')->getXmlrpcUrl();
        $auth = $this->getAPIAuthArray();

        // If these values are not set, we are unable to continue
        if(empty($xmlrpc_url) || $auth == false) {
            return false;
        }

        // Add the $auth-array as first 
        array_unshift( $params, $auth );

        // Initialize the XML-RPC client
        require_once 'Zend/XmlRpc/Client.php';
        $client = new Zend_XmlRpc_Client($xmlrpc_url);
        $client->setSkipSystemLookup(true);

        // Call the XML-RPC server
        try {
            $client->call($method, $params);
            return true;

        } catch (Exception $e) {
            Mage::getSingleton('magebridge/debug')->warning('XML-RPC client to method "'.$method.'" failed: '.$e->getMessage());
            Mage::getSingleton('magebridge/debug')->trace('Joomla! host', $xmlrpc_url);
            Mage::getSingleton('magebridge/debug')->trace('Method arguments', $params);
        }
        return false;
    }

    /*
     * Method that returns API-authentication-data as a basic array
     */
    public function getAPIAuthArray() 
    {
        $api_user = Mage::helper('magebridge')->getApiUser();
        $api_key = Mage::helper('magebridge')->getApiKey();
        if(empty($api_user) || empty($api_key)) {
            Mage::getSingleton('magebridge/debug')->warning('Listener getAPIAuthArray: api_user or api_key is missing');
            Mage::getSingleton('magebridge/debug')->trace('Listener: Meta data', Mage::getSingleton('magebridge/core')->getMetaData());
            return false;
        }

        $auth = array(
            'api_user' => Mage::getSingleton('magebridge/encryption')->encrypt($api_user),
            'api_key' => Mage::getSingleton('magebridge/encryption')->encrypt($api_key),
        );
        return $auth;
    }
} 
