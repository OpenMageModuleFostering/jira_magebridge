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

if(!defined('MAGEBRIDGE_DEBUG_TRACE')) define( 'MAGEBRIDGE_DEBUG_TRACE', 1 );
if(!defined('MAGEBRIDGE_DEBUG_NOTICE')) define( 'MAGEBRIDGE_DEBUG_NOTICE', 2 );
if(!defined('MAGEBRIDGE_DEBUG_WARNING')) define( 'MAGEBRIDGE_DEBUG_WARNING', 3 );
if(!defined('MAGEBRIDGE_DEBUG_ERROR')) define( 'MAGEBRIDGE_DEBUG_ERROR', 4 );
if(!defined('MAGEBRIDGE_DEBUG_FEEDBACK')) define( 'MAGEBRIDGE_DEBUG_FEEDBACK', 5 );

define( 'MAGEBRIDGE_DEBUG_TYPE_JOOMLA', 'joomla' );
define( 'MAGEBRIDGE_DEBUG_TYPE_MAGENTO', 'magento' );
define( 'MAGEBRIDGE_DEBUG_TYPE_XMLRPC', 'xmlrpc' );


/*
 * Stand-alone function to override the default error-handler.
 * This function is called from magebridge/core.
 */
function Jira_MageBridge_ErrorHandler($errno, $errstr, $errfile, $errline)
{
    // Flag which decides to close the bridge or not
    $close_bridge = false;

    // Handle each error-type differently
    switch($errno) {

        // With errors, we need to close the bridge and exit
        case E_USER_ERROR:
            Mage::getSingleton('magebridge/debug')->error("PHP Fatal Error in $errfile - Line $errline: $errstr");
            $close_bridge = true;
            break;

        // Log warnings
        case E_USER_WARNING:
            Mage::getSingleton('magebridge/debug')->warning("PHP Warning in $errfile - Line $errline: $errstr");
            break;

        // E_WARNING also includes Autoload.php messages which are NOT interesting
        case E_WARNING:
            break;
            
        // Ignore notices
        case E_USER_NOTICE:
            break;
            
        // Log unknown errors also as warnings, because we are in a E_STRICT environment
        default:
            // @todo: Can we detect notices here?
            Mage::getSingleton('magebridge/debug')->warning("PHP Unknown in $errfile - Line $errline: [$errno] $errstr");
            break; 
    }

    // Close the bridge if needed
    if($close_bridge == true) {
        $bridge = Mage::getSingleton('magebridge/core');
        $bridge->output();
        exit(1);
    }

    return true;
}

/*
 * Stand-alone function to override the default exception-handler.
 * This function is called from magebridge/core.
 */
function Jira_MageBridge_ExceptionHandler($exception)
{
    Mage::getSingleton('magebridge/debug')->error("PHP Fatal Error: ".$exception->getMessage());
    $bridge = Mage::getSingleton('magebridge/core');
    print $bridge->output(false);
    return;
}

/*
 * MageBridge Debug-class
 */
class Jira_MageBridge_Model_Debug
{
    protected static $_instance = null;
    private $_data = array();

    static public function getInstance()
    {
        if(null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function clean()
    {
        $this->_data = array();
    }

    public function add($message = null, $type = MAGEBRIDGE_DEBUG_NOTICE, $time = null, $origin = null)
    {
        if(!empty($message)) {
            if(empty($time) || !$time > 0) $time = time();
            if(empty($origin)) $origin = 'Magento';

            $data = array( 
                'type' => $type, 
                'message' => $message, 
                'time' => $time,
                'origin' => $origin,
            );
        
            $message = '['.$data['origin'].'] ';
            $message .= '('.date('Y-m-d H:i:s', $data['time']).') ';
            $message .= $data['type'] . ': ';
            $message .= $data['message'] . "\n";

            $this->_data[] = $data;
        }
    }

    public function notice($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_NOTICE, $time);
    }

    public function warning($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_WARNING, $time);
    }

    public function error($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_ERROR, $time);
    }

    public function trace($message = null, $variable = null, $time = null)
    {
        if(!empty($variable)) {
            $message = $message.': '.var_export($variable, true);
        } else {
            $message = $message.': NULL';
        }
        $this->add($message, MAGEBRIDGE_DEBUG_TRACE, $time);
    }

    public function feedback($message = null, $variable, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_FEEDBACK, $time);
    }
}
