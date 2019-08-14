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

define( 'MAGEBRIDGE_AUTHENTICATION_FAILURE', 0 );
define( 'MAGEBRIDGE_AUTHENTICATION_SUCCESS', 1 );
define( 'MAGEBRIDGE_AUTHENTICATION_ERROR', 2 );

class Jira_MageBridge_Model_User extends Mage_Core_Model_Abstract
{
    /**
     * Data
     */
    var $_data = null;

    /*
     * Perform a Single Sign On if told so in the bridge-request
     */
    public function doSSO()
    {
        // Get the SSO-flag from $_GET
        $sso = Mage::app()->getRequest()->getQuery('sso');
        $app = Mage::app()->getRequest()->getQuery('app');

        if(!empty($sso) && !empty($app)) {

            switch($sso) {
                case 'logout':
                    $this->doSSOLogout($app);
                    return true;

                case 'login':
                    $this->doSSOLogin($app);
                    return true;
            }
        }

        return false;
    }

    /*
     * Perform a Single Sign On logout
     */
    public function doSSOLogout($app) {

        Mage::getSingleton('magebridge/debug')->notice('doSSOLogin('.$app.'): '.session_id());

        // Initialize the session and end it
        if($app == 'admin') {

            Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));

            $session = Mage::getSingleton('adminhtml/session');
            $session->unsetAll();
            setcookie( 'adminhtml', null );
            session_destroy();

        } else {

            Mage::getSingleton('core/session', array('name'=>'frontend'));
            Mage::getSingleton('customer/session')->logout();
            setcookie( 'frontend', null );
            session_destroy();

        }

        // Send the logs through XML-RPC before exiting
        Mage::getSingleton('magebridge/client')->addDebug();
        Mage::getSingleton('magebridge/client')->multicall();

        // Redirect
        header( 'HTTP/1.1 302');
        header( 'Location: '.base64_decode(Mage::app()->getRequest()->getQuery('redirect')));
        return true;
    }
    
    /*
     * Perform a Single Sign On login
     */
    public function doSSOLogin($app) {

        Mage::getSingleton('magebridge/debug')->notice('doSSOLogin ['.$app.']: '.session_id());

        // Construct the redirect back to Joomla!
        $host = null;
        $arguments = array(
            'option=com_magebridge',
            'task=login',
        );

        // Loop to detect other variables
        foreach(Mage::app()->getRequest()->getQuery() as $name => $value) {
            if($name == 'base') $host = base64_decode($value);
            if($name == 'token') $token = $value;
        }
        $hash = Mage::app()->getRequest()->getQuery('hash');

        // Backend / frontend login
        if($app == 'admin') {
            $newhash = $this->doSSOLoginAdmin($token, $hash);
        } else {
            $newhash = $this->doSSOLoginCustomer($token, $hash);
        }

        //$arguments[] = 'session='.session_id(); // Hand back the session-ID so we can adapt the Joomla!-Magento session as well
        $arguments[] = 'hash='.$newhash; // @todo: What is done with this?
        $arguments[] = $token.'=1';

        // Send the logs through XML-RPC before exiting
        Mage::getSingleton('magebridge/client')->addDebug();
        Mage::getSingleton('magebridge/client')->multicall();

        // Redirect
        header( 'HTTP/1.1 302');
        header( 'Location: '.$host.'index.php?'.implode('&', $arguments ));
        return true;
    }

    /*
     * Perform an customer SSO login
     */
    public function doSSOLoginCustomer($token, $hash) {

        // Initialize the session
        Mage::getSingleton('core/session', array('name'=>'frontend'));
        $session = Mage::getSingleton('customer/session');

        // Initialize the customer
        $customer = $session->getCustomer();
        $customer->loadByEmail(stripslashes(Mage::app()->getRequest()->getQuery('user')));

        Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [frontend]: customer-name '.$customer->getName());

        // Save the customer in the actual data if this simple authentication succeeds
        $newhash = md5($token.$customer->getPasswordHash());
        if($hash == $newhash) {
            $session->setCustomerAsLoggedIn($customer);
            session_regenerate_id();
            setcookie('frontend', session_id());

        } else {
            Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [frontend]: mismatch');
        }

        return $newhash;
    }

    /*
     * Perform an admin SSO login
     */
    public function doSSOLoginAdmin($token, $hash) {

        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        if(isset($_COOKIE['adminhtml'])) {
            Mage::getSingleton('adminhtml/session')->setSessionId($_COOKIE['adminhtml']);
        }

        $username = stripslashes(Mage::app()->getRequest()->getQuery('user'));
        $user = Mage::getSingleton('admin/user');
        $user->loadByUsername($username);
        $newhash = md5($token.md5($user->getPassword()));

        if($user->getId() && $hash == $newhash) {

            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }

            // Initialize the session
            $session = Mage::getSingleton('admin/session');
            if($session->getAdmin() == null || $session->getAdmin()->getId() == false) {

                Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [admin]: Session take-over for user '.$username);
                //$session->setIsFirstVisit(true); // @todo: Try this out.
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                //$session->revalidateCookie();

                session_regenerate_id();
                setcookie('adminhtml', session_id());

            }

        } else {
            Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [admin]: mismatch');
        }

        return $newhash;
    }

    /*
     * Perform an user-login
     */
    public function login($data)
    {
        $data['username'] = Mage::getSingleton('magebridge/encryption')->decrypt($data['username'], 'customer username');
        $data['password'] = Mage::getSingleton('magebridge/encryption')->decrypt($data['password'], 'customer password');

        switch($data['application']) {
            case 'admin':
                return $this->loginAdmin($data);

            default:
                return $this->loginCustomer($data);
        }
    }

    /*
     * Perform an customer-login
     */
    public function loginCustomer($data) {

        $username = $data['username'];
        $password = $data['password'];

        try {
            $session = Mage::getSingleton('customer/session');
        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to start customer session');
            return $data;
        }

        try {

            if($session->login($username, $password)) {

                Mage::getSingleton('magebridge/debug')->notice('Login of '.$username);
                $customer = $session->getCustomer();
                $session->setCustomerAsLoggedIn($customer);

                $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                $data['email'] = $customer->getEmail();
                $data['fullname'] = $customer->getName();
                $data['hash'] = $customer->getPasswordHash();

            } else {
            
                Mage::getSingleton('magebridge/debug')->error('Login failed');
                $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to login customer "'.$username.'": '.$e->getMessage());
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform an admin-login
     */
    public function loginAdmin($data) {

        $username = $data['username'];
        $password = $data['password'];

        try {

            Mage::getSingleton('magebridge/debug')->notice('Admin login of '.$username);

            $user = Mage::getSingleton('admin/user');
            $user->login($username, $password);
            if($user->getId()) {

                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
                $session = Mage::getSingleton('admin/session');
                $session->setIsFirstVisit(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                
                session_regenerate_id();

                $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                $data['email'] = null;
                $data['fullname'] = null;
                $data['hash'] = md5($user->getPassword());

            } else {
            
                Mage::getSingleton('magebridge/debug')->error('Admin login failed');
                $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to login admin: '.$e->getMessage());
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform a logout
     */
    public function logout($data)
    {
        Mage::getSingleton('magebridge/debug')->notice('Logout customer');
        try {
            $session = Mage::getSingleton('customer/session');
            $session->logout();
            $data['state'] = 0;

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to logout customer: '.$e->getMessage());
            $data['state'] = 2;
        }

        return $data;
    }

}
