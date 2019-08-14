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

class Jira_MageBridge_Model_User_Api extends Mage_Api_Model_Resource_Abstract
{
    /*
     * API-method to save a customer to the database
     */
    public function save($data = array())
    {
        if(empty($data) || !isset($data['email'])) {
            Mage::getSingleton('magebridge/debug')->warning('No email in data');
            return false;
        }

        try {
            // Initialize the session
            Mage::getSingleton('core/session', array('name'=>'frontend'));
            $session = Mage::getSingleton('customer/session');

            // Initialize the customer and its address
            $customer = $session->getCustomer();
            $customer->loadByEmail(stripslashes($data['username']));
            $address = $customer->getPrimaryBillingAddress();

            // Load the new data
            foreach($data as $name => $value) {
                if(isset($name)) {
                    $method = 'set';
                    $array = explode('_', $name);
                    foreach($array as $i => $a) {
                        if($a == 'address' && $i == 0 && count($array) > 1) continue;
                        $method .= ucfirst($a);
                    }

                    if(preg_match('/^address_/', $name)) {
                        $address->$method($value);
                    } else {
                        $customer->$method($value);
                    }
                }
            }

            // Set the customer group
            if(!empty($data['customer_group'])) $customer->setGroupId($data['customer_group']);

            // Try to validate and save this customer
            try {
                $validation = $customer->validate();
                if(is_array($validation) && count($validation) > 0) {
                    foreach($validation as $v) {
                        Mage::getSingleton('magebridge/debug')->warning('Customer validation failed: '.$v);
                    }
                    return false;
                }
            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Customer validation crashed: '.$e->getMessage());
                return false;
            }

            $customer->save();
            if($customer->save() == false) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save customer '.$customer->getEmail());
            }

            // Try to validate and save this address
            try {
                $validation = $address->validate();
                if(is_array($validation) && count($validation) > 0) {
                    foreach($validation as $v) {
                        Mage::getSingleton('magebridge/debug')->warning('Address validation failed: '.$v);
                    }
                    return false;
                }
            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Address validation crashed: '.$e->getMessage());
                return false;
            }

            $address->save();
            if($address->save() == false) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save address '.$customer->getEmail());
            }

            // Save the password if needed
            if(isset($data['password_clear'])) {
                $data['password_clear'] = trim($data['password_clear']);
                if(!empty($data['password_clear'])) {
                    $password = Mage::getSingleton('magebridge/encryption')->decrypt($data['password_clear']);
                    $customer->changePassword($password);
                    $data['hash'] = $customer->getPasswordHash();
                }
            }

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load customer: '.$e->getMessage());
        }

        return $data;
    }

    /*
     * API-method to delete a customer 
     */
    public function delete($data = array())
    {
        // Use this, to make sure Magento thinks it's dealing with the right app.
        // Otherwise _protectFromNonAdmin() will make this fail.
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));

        // Initialize the customer
        $customer = Mage::getModel('customer/customer');
        if(!empty($data['website_id'])) {
            Mage::getSingleton('magebridge/debug')->error('Set website-ID to: '.$data['website_id']);
            $customer->setWebsiteId($data['website_id']);
        }
        $customer->loadByEmail(stripslashes($data['username']));

        // Delete the customer
        if($customer->getId()) {
            $customer->delete();
            return true;
        }

        return false;
    }
}
