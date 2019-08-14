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

class Jira_MageBridge_Model_Update extends Mage_Core_Model_Abstract
{
    private $_current_version = null;
    private $_new_version = null;
    private $_remote_url = 'http://downloader.yireo.com';

    public function getApiLink($arguments = array())
    {
        $url = 'http://api.yireo.com/';
        $arguments = array_merge($this->getApiArguments(), $arguments);

        foreach($arguments as $name => $value) {
            if($name == 'request') {
                $arguments[$name] = "$value";
            } else {
                $arguments[$name] = "$name,$value";
            }
        }

        return $url . implode('/', $arguments);
    }

    public function getApiArguments()
    {
        return array(
            'license' => $this->getLicenseKey(),
            'domain' => $_SERVER['HTTP_HOST'],
        );
    }

    public function upgradeNeeded()
    {
        if((int)$this->getNewVersion() > (int)$this->getCurrentVersion()) {
            return true;
        }
        return true;
    }

    public function doUpgrade()
    {
        $download_url = $this->getApiLink(array('resource' => 'download', 'request' => 'Jira_MageBridge.tar.gz'));

        try {
            ini_set('error_reporting', 0);
            $paths = explode(':', ini_get('include_path'));
            array_unshift($paths, './downloader');
            array_unshift($paths, './downloader/pearlib/php');
            ini_set('include_path', implode(':', $paths));

            require_once 'PEAR/Frontend.php';
            require_once 'Maged/Pear/Frontend.php';
            require_once 'Maged/Pear.php';

        } catch(Exception $e) {
            return 'Failed to prepare PEAR: '.$e->getMessage();
        }

        $pear = new Maged_Pear();
        $params = array(
            'command' => 'install', 
            'options' => array('force' => 1), 
            'params' => array($download_url),
        );

        if(is_dir(dirname(__FILE__).DS.'.svn')) {
            return 'Updating Subversion environments is not allowed';
        }

        try {
            $run = new Maged_Model_Pear_Request($params);
            if($command = $run->get('command')) {
                $cmd = PEAR_Command::factory($command, $pear->getConfig());
                $result = $cmd->run($command, $run->get('options'), $run->get('params'));
                if(is_bool($result)) ($result == true) ? 'ok' : 'failed';
                
                // Reset the configuration cache
                Mage::getConfig()->removeCache();

                return 'PEAR result: '.(string)$result;
            } else {
                return 'Maged_Model_Pear_Request failed';
            }
        } catch(Exception $e) {
            return 'Failed to initialize PEAR: '.$e->getMessage();
        }

        return 'Upgrade failed by unknown error';
    }

    public function getCurrentVersion()
    {
        if(empty($this->_current_version)) {
            $config = Mage::app()->getConfig()->getModuleConfig('Jira_MageBridge');
            $this->_current_version = (string)$config->version;
        }
        return $this->_current_version;
    }

    public function getNewVersion()
    {
        if(empty($this->_new_version)) {
            $arguments = array('resource' => 'versions', 'request' => 'downloads/magebridge');
            $url = $this->getApiLink($arguments);
            $this->_data = $this->_getRemote($url);
            try {
                $doc = new SimpleXMLElement($this->_data);
            } catch(Exception $e) {
                return 'Update check failed. Is your licensing correct?';
                //return 'XML-parsing failed in data: '.$this->_data.' :'.$e->getMessage();
            }
            $this->_new_version = (string)$doc->magento;
        }
        return $this->_new_version;
    }

    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/settings/license_key');
    }

    private function _getRemote($url)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->write(Zend_Http_Client::GET, $url, '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        return $data;
    }
}
