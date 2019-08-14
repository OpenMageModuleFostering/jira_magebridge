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

/**
 * MageBridge admin controller
 */
class Jira_MageBridge_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Common method
     */
    protected function _initAction()
    {
        // Give a warning if Mage::getResourceModel('api/user_collection') returns zero
        $collection = Mage::getResourceModel('api/user_collection');
        if(!count($collection) > 0) {
            Mage::getModel('adminhtml/session')->addError('You have not configured any API-user yet.');
        }

        // Give a warning if Mage::getResourceModel('api/user_collection') returns zero
        $store = Mage::app()->getStore(Mage::getModel('magebridge/core')->getStore());
        if($store->getConfig('catalog/seo/product_url_suffix') == '.html' || $store->getConfig('catalog/seo/category_url_suffix') == '.html') {
            Mage::getModel('adminhtml/session')->addError('You have configured the URL-suffix ".html" which conflicts with Joomla!. Check out the Yireo-site for more information.');
        }

        // Give a warning if Configuration Cache is enabled
        if(Mage::app()->useCache('config')) {
            Mage::getModel('adminhtml/session')->addError('Please turn OFF the Configuration Cache. This degrades the performance of MageBridge.');
        }

        // Load the layout
        $this->loadLayout()
            ->_setActiveMenu('cms/magebridge')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('CMS'), Mage::helper('adminhtml')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('MageBridge'), Mage::helper('adminhtml')->__('MageBridge'))
        ;

        return $this;
    }

    /**
     * Settings page
     */
    public function indexAction()
    {
        if(strlen(Mage::helper('magebridge')->getLicenseKey()) == '') {
            $block = 'license';
        } else {
            $block = 'settings';
        }
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/'.$block))
            ->renderLayout();
    }

    /**
     * Settings page
     */
    public function settingsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/settings'))
            ->renderLayout();
    }

    /**
     * License page
     */
    public function licenseAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/license'))
            ->renderLayout();
    }

    /**
     * Updates page (which calls for AJAX)
     */
    public function updatesAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/updates'))
            ->renderLayout();
    }

    /**
     * Credits page
     */
    public function creditsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/credits'))
            ->renderLayout();
    }

    /**
     * Perform an update through AJAX
     */
    public function doupdateAction()
    {
        $update = Mage::getSingleton('magebridge/update');
        if($update->upgradeNeeded() == true) {
            $status = $update->doUpgrade();
        } else {
            $status = 'No upgrade needed';
        }

        $response = new Varien_Object();
        $response->setError(0);
        $response->setMessage($status);
        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction()
    {
        $page = 'magebridge/index/index';
        if ($data = $this->getRequest()->getPost()) {
                
            if(isset($data['license_key'])) {
                Mage::getConfig()->saveConfig('magebridge/settings/license_key', trim($data['license_key']));
                $page = 'magebridge/index/license';
            }

            if(isset($data['bridge_all'])) {
                Mage::getConfig()->saveConfig('magebridge/settings/bridge_all', (int)$data['bridge_all']);
                $page = 'magebridge/index/settings';
            }

            //if(isset($data['xmlrpc_url'])) {
            //    Mage::getConfig()->saveConfig('magebridge/settings/xmlrpc_url', trim($data['xmlrpc_url']));
            //}

            if(!empty($data['event_forwarding'])) {
                foreach($data['event_forwarding'] as $name => $value) {
                    Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$name, $value);
                }
            }

            Mage::getModel('adminhtml/session')->addSuccess('Settings saved');
            Mage::getConfig()->removeCache();
            
        }

        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    public function resetAction()
    {
        $page = 'magebridge/index/index';

        $config = Mage::getStoreConfig('magebridge/settings');
        if(!empty($config)) {
            foreach($config as $name => $value) {
                if($name != 'license_key') {
                    if(is_array($value)) {
                        // @todo: Reset events as well?
                    } else {
                        Mage::getConfig()->deleteConfig('magebridge/settings/'.$name);
                    }
                }
            }
        }

        Mage::getConfig()->removeCache();
        Mage::getModel('adminhtml/session')->addSuccess('Settings are reset to default');
            
        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    public function recommendedAction()
    {
        $page = 'magebridge/index/index';

        $events = Mage::getModel('magebridge/listener')->getEvents();
        foreach($events as $event) {
            Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$event[0], $event[1]);
        }

        Mage::getConfig()->removeCache();
        Mage::getModel('adminhtml/session')->addSuccess('Settings are reset to their recommended value');
            
        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }
}
