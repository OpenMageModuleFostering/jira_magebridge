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
 * Main bridge-class which handles the Magento configuration
 */
class Jira_MageBridge_Model_Core extends Mage_Core_Model_Abstract
{
    /*
     * Bridge-request
     */
    protected $_request = array();

    /*
     * Bridge-request
     */
    protected $_response = array();

    /*
     * Meta-data
     */
    protected $_meta = array();

    /*
     * System messages
     */
    protected $_messages = array();

    /*
     * System events
     */
    protected $_events = array();

    /*
     * Initialize the bridge-core
     */
    public function init($meta = null, $request = null)
    {
        // Set meta and request
        $this->_meta = $meta;
        $this->_request = $request;

        // Fill the response with the current request
        $this->setResponseData($request);

        // Decrypt everything that needs decrypting
        $this->_meta['api_user'] = $this->decrypt($this->getMetaData('api_user'));
        $this->_meta['api_key'] = $this->decrypt($this->getMetaData('api_key'));

        //Mage::getSingleton('magebridge/debug')->trace('Dump of meta', $this->_meta);
        //Mage::getSingleton('magebridge/debug')->trace('Dump of GET', $_GET);
        //Mage::getSingleton('magebridge/debug')->trace('Dump of response', $this->_response);

        // Overwrite the default error-handling by routing all magebridge/debug
        set_error_handler('Jira_MageBridge_ErrorHandler');
        set_exception_handler('Jira_MageBridge_ExceptionHandler');

        // Set the magebridge-URLs
        $this->setConfig();

        // Set the current store of this request
        try {
            Mage::app()->setCurrentStore(Mage::app()->getStore($this->getStore()));
        } catch( Exception $e ) {
            Mage::getSingleton('magebridge/debug')->error('Failed to intialize store "'.$this->getStore().'":'.$e->getMessage());
            // Do not return, but just keep on going with the default configuration
        }

        // Try to initialize the session
        try {
            $session = Mage::getSingleton('core/session', array('name'=>'frontend'));
        } catch( Exception $e ) {
            Mage::getSingleton('magebridge/debug')->error('Unable to instantiate core/session: '.$e->getMessage());
            return false;
        }

        return true;
    }

    /*
     * Method to change the regular Magento configuration as needed
     */
    public function setConfig()
    {
        // To start with, check the Joomla! configuration
        $this->checkJoomlaConfig();

        // Initialize the Magento configuration
        try {
            // Get the current store
            $store = Mage::app()->getStore($this->getStore());
            Mage::getSingleton('magebridge/debug')->notice('Set URLs of store "'.$store->getName().'" to '.$this->getMageBridgeUrl());

            // Check if the Configuration Cache is enabled, and if so, empty it
            /*
            if(Mage::app()->useCache('config')) {
                Mage::getSingleton('magebridge/debug')->warning('Configuration Cache is enabled, which is NOT recommended');
                $store->resetConfig();
            }
            */

            // Get the URLs from the Configuration
            $base_url = $store->getConfig('web/unsecure/base_url');
            $base_media_url = $store->getConfig('web/unsecure/base_media_url');
            $base_skin_url = $store->getConfig('web/unsecure/base_skin_url');
            $base_js_url = $store->getConfig('web/unsecure/base_js_url');
            if($store->getConfig('magebridge/settings/bridge_all') == 1) {
                $proxy = 'index.php?option=com_magebridge&view=proxy&url=';
                $base_media_url = str_replace($base_url, $proxy, $base_media_url);
                $base_skin_url = str_replace($base_url, $proxy, $base_skin_url);
                $base_js_url = str_replace($base_url, $proxy, $base_js_url);
            }

            // Set the URLs to point to Joomla!
            $store->setConfig('web/unsecure/base_url', $this->getMageBridgeUrl());
            $store->setConfig('web/unsecure/base_link_url', $this->getMageBridgeUrl());
            $store->setConfig('web/unsecure/base_media_url', $base_media_url);
            $store->setConfig('web/unsecure/base_skin_url', $base_skin_url);
            $store->setConfig('web/unsecure/base_js_url', $base_js_url);

            $store->setConfig('web/secure/base_url', $this->getMageBridgeUrl());
            $store->setConfig('web/secure/base_link_url', $this->getMageBridgeUrl());
            $store->setConfig('web/secure/base_media_url', $base_media_url);
            $store->setConfig('web/secure/base_skin_url', $base_skin_url);
            $store->setConfig('web/secure/base_js_url', $base_js_url);

            // Other manual settings
            $store->setConfig('web/seo/use_rewrites', 1);
            $store->setConfig('web/session/use_remote_addr', 0);
            $store->setConfig('web/session/use_http_via', 0);
            $store->setConfig('web/session/use_http_x_forwarded_for', 0);
            $store->setConfig('web/session/use_http_user_agent', 0);
            $store->setConfig('web/cookie/cookie_domain', '');
            //$store->setConfig('catalog/seo/product_url_suffix', '');
            //$store->setConfig('catalog/seo/category_url_suffix', '');

            // Rewrite the session lifetime
            if($this->getMetaData('joomla_conf_lifetime') > 0) {
                $store->setConfig('admin/security/session_cookie_lifetime', $this->getMetaData('joomla_conf_lifetime'));
                $store->setConfig('web/cookie/cookie_lifetime', $this->getMetaData('joomla_conf_lifetime'));
            }

            // Make sure we do not use SID= in the URL
            Mage::getModel('core/url')->setUseSession(false);
            Mage::getModel('core/url')->setUseSessionVar(true);

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Unable to set URLs to '.$this->getMageBridgeUrl().': '.$e->getMessage());
        }

        return true;
    }

    /*
     * Method to authenticate usage of the MageBridge API
     */
    public function checkJoomlaConfig()
    {
        // List of keys (meta => conf)
        $keys = array(
            'xmlrpc_url' => 'xmlrpc_url',
            'api_user' => 'api_user',
            'api_key' => 'api_key',
        );

        // Check the Joomla! settings
        foreach($keys as $meta_key => $conf_key) {
            $conf_value = Mage::getStoreConfig('magebridge/settings/'.$conf_key);
            $meta_value = $this->getMetaData($meta_key);
            if(empty($conf_value)) {
                Mage::getConfig()->saveConfig('magebridge/settings/'.$conf_key, $meta_value);
            }
        }
    }

    /*
     * Method to get the currently defined API-user
     */
    public function getApiUser()
    {
        $api_user_id = Mage::getStoreConfig('magebridge/settings/api_user_id');

        if(!$api_user_id > 0) {
            $collection = Mage::getResourceModel('api/user_collection');
            foreach($collection as $user) {
                $api_user_id = $user->getId();
                break;
            }
        }

        $api_user = Mage::getModel('api/user')->load($api_user_id);
        return $api_user;
    }

    /*
     * Method to authenticate usage of the MageBridge API
     */
    public function authenticate()
    {
        // Fetch the variables from the meta-data
        $api_session = $this->getMetaData('api_session');
        $api_user = $this->getMetaData('api_user');
        $api_key = $this->getMetaData('api_key');

        // If the API-session matches, we don't need authenticate any more
        if($api_session == md5(session_id().$api_user.$api_key)) {
            return true;
        }

        // If we still need authentication, authenticate against the Magento API-class
        try {
            $api = Mage::getModel('api/user');
            if( $api->authenticate($api_user, $api_key) == true ) {
                $this->setMetaData('api_session', md5(session_id().$api_user.$api_key));
                return true;
            }

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Exception while authorizing: '.$e->getMessage());
        }
        return false;
    }

    /*
     * Method to catch premature output in case of AJAX-stuff
     */
    public function preoutput()
    {
        // Initialize the frontcontroller
        $controller = Mage::getSingleton('magebridge/core')->getController();

        // Start the buffer and fetch the output from Magento
        $body = Mage::app()->getResponse()->getBody();
        if(!empty($body)) {
            $controller->getResponse()->clearBody();
            return true;
        }

        return false;
    }

    /*
     * Method to output the regular bridge-data through JSON
     */
    public function output($complete = true)
    {
        if($complete) {
            $this->closeBridge();
        } else {
            $this->addResponseData('meta', array(
                'type' => 'meta',
                'data' => array(
                    'state' => $this->getMetaData('state'),
                    'extra' => $this->getMetaData('extra'),
                )
            ));
        }

        $debug = Mage::getSingleton('magebridge/debug')->getData();
        if($this->getMetaData('debug') == 1 && !empty($debug)) {
            $this->addResponseData('debug', array(
                'type' => 'debug',
                'data' => $debug,
            ));
        }

        // Output the response
        return json_encode($this->getResponseData());
    }
    
    /*
     * Method to close the bridge and add the final data
     */
    public function closeBridge()
    {
        // Add extra information
        $this->setMetaData('magento_session', session_id());
        $this->setMetaData('magento_version', Mage::getVersion());

        // Append customer-data
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->setMetaData('magento_customer', array(
            'fullname' => $customer->getName(),
            'username' => $customer->getEmail(),
            'email' => $customer->getEmail(),
            'hash' => $customer->getPasswordHash(),
        ));

        // Append store-data
        $store = Mage::app()->getStore($this->getStore());
        $this->setMetaData('magento_config', array(
            'catalog/seo/product_url_suffix' => $store->getConfig('catalog/seo/product_url_suffix'),
            'catalog/seo/category_url_suffix' => $store->getConfig('catalog/seo/category_url_suffix'),
            'admin/security/session_cookie_lifetime' => $store->getConfig('admin/security/session_cookie_lifetime'),
            'web/cookie/cookie_lifetime' => $store->getConfig('web/cookie/cookie_lifetime'),
            'magento_backend' => $this->getAdminPath(),
            'root_template' => $this->getRootTemplate(),
            /*
            'web/unsecure/base_url' => $store->getConfig('web/unsecure/base_url'),
            'web/unsecure/base_link_url' => $store->getConfig('web/unsecure/base_link_url'),
            'web/unsecure/base_media_url' => $store->getConfig('web/unsecure/base_media_url'),
            'web/unsecure/base_skin_url' => $store->getConfig('web/unsecure/base_skin_url'),
            'web/unsecure/base_js_url' => $store->getConfig('web/unsecure/base_js_url'),
            'web/secure/base_url' => $store->getConfig('web/secure/base_url'),
            'web/secure/base_link_url' => $store->getConfig('web/secure/base_link_url'),
            'web/secure/base_media_url' => $store->getConfig('web/secure/base_media_url'),
            'web/secure/base_skin_url' => $store->getConfig('web/secure/base_skin_url'),
            'web/secure/base_js_url' => $store->getConfig('web/secure/base_js_url'),
            'web/seo/use_rewrites' => $store->getConfig('web/seo/use_rewrites'),
            */
        ));

        // Start building the request
        $messages = $this->getMessages();
        if(!empty($messages)) {
            $this->addResponseData('messages', array(
                'type' => 'messages',
                'data' => $messages,
            ));
        }

        $events = $this->getEvents();
        if(!empty($events)) {
            $this->addResponseData('events', array(
                'type' => 'events',
                'data' => $events,
            ));
        }

        $metadata = $this->getMetaData();
        if(!empty($metadata)) {
            $this->addResponseData('meta', array(
                'type' => 'meta',
                'data' => $metadata,
            ));
        }
    }

    /*
     * Helper-function to parse Magento output for usage in Joomla!
     */
    public function parse($string)
    {
        $string = str_replace(Mage::getUrl(), $this->getMageBridgeUrl(), $string);
        return $string;
    }

    /*
     * Return the path to the Magento Admin Panel
     */
    public function getAdminPath()
    {
        $routeName = 'adminhtml';
        $route = Mage::app()->getFrontController()->getRouterByRoute($routeName);
        $backend = $route->getFrontNameByRoute($routeName);
        return $backend;
    }

    /*
     * Return the current page layout for the Magento theme
     */
    public function getRootTemplate()
    {
        $block = Mage::getModel('magebridge/block')->getBlock('root');
        if(!empty($block)) {
            return $block->getTemplate();
        }
        return 'none';
    }

    /*
     * Helper-method to get the Front-controller
     */
    public static function getController()
    {
        static $controller;
        if(empty($controller)) {
            $controller = Mage::app()->getFrontController()->setNoRender(true)->dispatch();
            $controller->getAction()->getLayout()->removeOutputBlock('root');
        }
        return $controller;
    }

    /*
     * Helper-method to get the bridge-request
     */
    public function getRequestData()
    {
        return $this->_request;
    }

    /*
     * Helper-method to get the bridge-response
     */
    public function getResponseData()
    {
        return $this->_response;
    }

    /*
     * Helper-method to set the bridge-response
     */
    public function setResponseData($data)
    {
        $this->_response = $data;
        return null;
    }

    /*
     * Helper-method to add some data to the bridge-response
     */
    public function addResponseData($name = null, $data)
    {
        $this->_response[$name] = $data;
        return true;
    }

    /*
     * Helper-method to get the meta-data
     */
    public function getMetaData($name = null)
    {
        if($name == null) {
            return $this->_meta;
        } elseif(isset($this->_meta[$name])) {
            return $this->_meta[$name];
        } else {
            return null;
        }
    }

    /*
     * Helper-method to set the meta-data
     */
    public function setMetaData($name = '', $value = '')
    {
        $this->_meta[$name] = $value;
        return null;
    }

    /*
     * Helper-method to get the system messages
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /*
     * Helper-method to set the system messages
     */
    public function setMessages($messages)
    {
        $this->_messages = $messages;
        return null;
    }

    /*
     * Helper-method to get the system events from the session and clean up afterwards
     */
    public function getEvents()
    {
        $events = Mage::getSingleton('magebridge/session')->getEvents();;
        Mage::getSingleton('magebridge/session')->cleanEvents();
        return $events;
    }

    /*
     * Helper-method to set the system events
     */
    public function setEvents($events)
    {
        $this->_events = $events;
        return null;
    }

    /*
     * Helper-method to get the Joomla! URL from the meta-data
     */
    public function getMageBridgeUrl()
    {
        return $this->getMetaData('joomla_url');
    }

    /*
     * Helper-method to get the Joomla! SEF URL from the meta-data
     */
    public function getMageBridgeSefUrl()
    {
        return $this->getMetaData('joomla_sef_url');
    }

    /*
     * Helper-method to get the requested store-name from the meta-data
     */
    public function getStore()
    {
        return $this->getMetaData('store');
    }

    /*
     * Return the configured license key
     */
    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/settings/license_key');
    }

    /*
     * Return the current session ID
     */
    public function getMageSession()
    {
        return session_id();
    }

    /*
     * Encrypt data for security
     */
    public function encrypt($data)
    {
        return Mage::getSingleton('magebridge/encryption')->encrypt($data);
    }

    /*
     * Decrypt data after encryption
     */
    public function decrypt($data)
    {
        return Mage::getSingleton('magebridge/encryption')->decrypt($data);
    }
}
