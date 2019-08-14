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

// Use this for profiling
$init_time = time();

// Initialize the bridge
require_once 'magebridge.class.php';
$magebridge = new MageBridge();

// Mask this request
$magebridge->premask();

// Initialize the Magento application
require_once 'app/Mage.php';
try {
    $app_value = $magebridge->getMeta('app_value');
    $app_type = $magebridge->getMeta('app_type');
    $app_time = time();

    if($app_type == 'website') {
        $app_value = (int)$app_value;
    }

    Mage::app($app_value, $app_type);
    Mage::getSingleton('magebridge/debug')->notice('Initializing', $init_time);
    Mage::getSingleton('magebridge/debug')->notice("Mage::app($app_value,$app_type)", $app_time);

} catch(Exception $e) {
    die("Mage::app('$app_value','$app_type') failed: ".$e->getMessage());
}

// Run the bridge
$magebridge->run();

// End
