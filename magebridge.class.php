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
 * MageBridge-class that acts like proxy between bridge-classes and the API
 */
class MageBridge 
{
    /*
     * The current request
     */
    private $request = array();

    /*
     * Constructor
     */
    public function __construct()
    {
        // Decode all the POST-values with JSON
        if(!empty($_POST)) {
            foreach($_POST as $index => $post) {
                $this->request[$index] = json_decode(stripslashes($post), true);
            }
        } elseif(!empty($_GET)) {
            foreach($_GET as $index => $get) {
                $this->request[$index] = json_decode(stripslashes($get), true);
            }
        }

        // Decode extra string values with Base64
        if(!empty($this->request['meta']['arguments']) && is_array($this->request['meta']['arguments'])) {
            foreach($this->request['meta']['arguments'] as $name => $value) {
                if(is_string($value)) {
                    $this->request['meta']['arguments'][$name] = base64_decode($value);
                }
            }
        }

        return $this->request;
    }

    /*
     * Get the request-data
     */
    public function getRequest()
    {
        return $this->request;
    }

    /*
     * Helper-function to get the meta-data from the request
     */
    public function getMeta($name = null) 
    {
        if(!empty( $this->request['meta']['arguments'] )) {
            if($name != null) {
                if(isset($this->request['meta']['arguments'][$name])) {
                    return $this->request['meta']['arguments'][$name];
                }
            } else {
                return $this->request['meta']['arguments'];
            }
        }
        return null;
    }

    /*
     * Mask this request by using the data sent along with this request
     */
    public function premask()
    {
        $data = $this->getMeta();

        // Initialize the right session 
        if(!empty($data['magento_session'])) {
            session_name('frontend');
            session_id($data['magento_session']);
            $_COOKIE['frontend'] = $data['magento_session'];

        } elseif(!empty($_GET['sso']) && !empty($_GET['app'])) {

            if($_GET['app'] == 'admin' && !empty($_COOKIE['adminhtml'])) {
                session_name('adminhtml');
                session_id($_COOKIE['adminhtml']);

            } elseif(!empty($_COOKIE['frontend'])) {
                session_name('frontend');
                session_id($_COOKIE['frontend']);
            }

        } elseif(empty($_COOKIE['frontend'])) {
            $_COOKIE = array();
        }

        // Mask the POST
        if(!empty($data['post'])) {
            $_POST = $data['post'];
        } elseif(!isset($_POST['mbtest'])) {
            $_POST = array();
        }

        // Mask the REQUEST_URI and the GET
        if(!empty($data['request_uri']) && strlen($data['request_uri']) > 0) {

            // Set the REQUEST_URI
            $data['request_uri'] = preg_replace( '/^\//', '', $data['request_uri']);
            $_SERVER['REQUEST_URI'] = $data['request_uri'];

            // Set the GET variables
            $data['request_uri'] = preg_replace( '/^\//', '', $data['request_uri']);
            $query = preg_replace( '/^([^\?]+)\?/', '', $data['request_uri'] );
            if($query != $data['request_uri']) {
                parse_str(rawurldecode($query), $parts);
                foreach($parts as $name => $value) {
                    $_GET[$name] = $value;
                }
            }

        } else {
            $_SERVER['REQUEST_URI'] = null;
        }

        // Mask the HTTP_USER_AGENT
        if(!empty($data['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $data['user_agent'];
        }

        // Mask the HTTP_REFERER
        if(!empty($data['http_referer'])) {
            $_SERVER['HTTP_REFERER'] = $data['http_referer'];
        }

        return true;
    }

    /*
     * Run the bridge-core
     */
    public function run()
    {
        Mage::getSingleton('magebridge/debug')->notice('Session: '.session_id());

        // Handle SSO
        if(Mage::getSingleton('magebridge/user')->doSSO() == true) {
            exit;
        }

        // Now Magento is initialized, we can load the MageBridge core-class
        $bridge = Mage::getSingleton('magebridge/core');
        $bridge->init($this->getMeta(), $this->getRequest());

        // Handle tests
        if(Mage::app()->getRequest()->getQuery('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'get');
            print $bridge->output(false);
            exit;
        } elseif(Mage::app()->getRequest()->getPost('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'post');
            print $bridge->output(false);
            exit;
        }

        // Match the license
        if($this->getMeta('license') != $bridge->getLicenseKey()) {
            $bridge->setMetaData('state', 'license failed');
            print $bridge->output(false);
            exit;
        }

        // Authorize this request using the API credentials (set in the meta-data)
        if($this->authenticate() == false) {
            $bridge->setMetaData('state', 'authentication failed');
            print $bridge->output(false);
            exit;
        }

        // Pre-fetch all the current messages, because the Magento bootstrap might clear them later
        $bridge->setMessages( Mage::getSingleton('magebridge/messages')->getMessages() );

        // Check if there's any output already set (for instance JSON, AJAX, XML, PDF) and output it right away
        if($bridge->preoutput() == true) {
            exit;
        }

        // Fetch the actual request
        $data = $bridge->getRequestData();
        if(is_array($data) && !empty($data)) {

            // Dispatch the request to the appropriate classes 
            Mage::getSingleton('magebridge/debug')->notice('Dispatching the request');
            $data = $this->dispatch($data);

            // Set the completed request as response
            $bridge->setResponseData($data);

        } else {
            Mage::getSingleton('magebridge/debug')->notice('Empty request');
        }

        Mage::getSingleton('magebridge/debug')->notice('Done with session: '.session_id());
        //Mage::getSingleton('magebridge/debug')->trace('Session dump', $_SESSION);
        //Mage::getSingleton('magebridge/debug')->trace('Cookie dump', $_COOKIE);
        //Mage::getSingleton('magebridge/debug')->trace('GET dump', $_GET);
        //Mage::getSingleton('magebridge/debug')->trace('POST dump', $_POST);

        ini_set('display_errors', 0);
        $bridge->setMetaData('state', null);
        print $bridge->output();
        exit;
    }

    /*
     * Authorize access to the bridge
     */
    public function authenticate()
    {
        // Authorize against the bridge-core
        $bridge = Mage::getSingleton('magebridge/core');

        if($bridge->authenticate() == false) {
            session_regenerate_id();
            Mage::getSingleton('magebridge/debug')->error('API authorization failed for user '.$bridge->getMetaData('api_user'));
            return false;

        } else {
            Mage::getSingleton('magebridge/debug')->notice('API authorization succeeded');
        }
        return true;
    }

    /*
     * Dispatch the bridge-request to the appropriate classes
     */
    public function dispatch($data)
    {
        // Loop through the posted data, complete it and send it back
        foreach($data as $index => $segment) {

            switch($segment['type']) {

                case 'authenticate':
                    $segment['data'] = Mage::getSingleton('magebridge/user')->login($segment['arguments']);
                    break;

                case 'urls':
                    $segment['data'] = Mage::getSingleton('magebridge/url')->getData($segment['name']);
                    break;

                case 'block':
                    $segment['data'] = Mage::getSingleton('magebridge/block')->getOutput($segment['name'], $segment['arguments']);
                    $segment['meta'] = Mage::getSingleton('magebridge/block')->getMeta($segment['name']);
                    break;

                case 'breadcrumbs':
                    $segment['data'] = Mage::getSingleton('magebridge/breadcrumbs')->getBreadcrumbs();
                    break;

                case 'api':
                    $segment['data'] = Mage::getSingleton('magebridge/api')->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'event':
                    $segment['data'] = Mage::getSingleton('magebridge/dispatcher')->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'headers':
                    $segment['data'] = Mage::getSingleton('magebridge/headers')->getHeaders();
                    break;
            }

            $data[$index] = $segment;
        }
        return $data;
    }
}

// End
