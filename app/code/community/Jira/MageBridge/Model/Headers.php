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

class Jira_MageBridge_Model_Headers extends Jira_MageBridge_Model_Block
{
    public static function getHeaders()
    {
        Mage::getSingleton('magebridge/debug')->notice('Load headers');
        try {
            $controller = Mage::getSingleton('magebridge/core')->getController();
            $controller->getAction()->renderLayout();

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        try {
            $head = $controller->getAction()->getLayout()->getBlock('head');
            if(!empty($head)) {
                $headers = $head->getData();
                foreach($headers['items'] as $index => $item) {

                    $item['path'] = null;
                    switch($item['type']) {

                        case 'js':
                        case 'js_css':
                            $item['path'] = 'js/'.$item['name'];
                            break;

                        case 'skin_js':
                        case 'skin_css':
                            $item['path'] = Mage::getDesign()->getSkinUrl($item['name']);
                            break;

                        default:
                            $item['path'] = null;
                            break;
                    }

                    $headers['items'][$index] = $item;
                }
                return $headers;
            }
            return false;

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get headers: '.$e->getMessage());
            return false;
        }
    }
}
