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
@todo Use a more generic way to fetch arguments from the event-object (getArguments?)
@todo New events:
- sales_convert_quote_to_order
- sales_order_invoice_cancel
- sales_order_invoice_pay
 */

class Jira_MageBridge_Model_Listener extends Mage_Core_Model_Abstract
{
    /*
     * Method to list all current events
     */
    public function getEvents()
    {
        return array(
            array('address_save_after', 1),
            array('admin_session_user_login_success', 0),
            array('adminhtml_customer_save_after', 1),
            array('adminhtml_customer_delete_after', 1),
            array('catalog_product_save_after', 0),
            array('catalog_product_delete_after', 0),
            array('catalog_category_save_after', 0),
            array('catalog_category_delete_after', 0),
            array('catalog_product_status_update', 0),
            array('checkout_cart_add_product_complete', 0),
            array('checkout_controller_onepage_save_shipping_method', 0),
            array('checkout_onepage_controller_success_action', 1),
            array('checkout_type_onepage_save_order_after', 0),
            array('customer_delete_after', 1),
            array('customer_login', 0),
            array('customer_logout', 0),
            array('customer_save_after', 1),
            array('sales_convert_order_to_quote', 0),
            array('sales_quote_save_after', 0),
            array('sales_quote_place_after', 0),
        );
    }

    /*
     * Method fired on the event <address_save_after>
     */
    public function addressSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $address = $observer->getEvent()->getObject();
        $arguments = array(
            'address' => $this->_getAddressArray($address),
        );

        $this->fireEvent('address_save_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <admin_session_user_login_success>
     */
    public function adminSessionUserLoginSuccess($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $user = $observer->getEvent()->getUser();
        $arguments = array(
            'user' => $this->_getUserArray($user),
        );

        $this->fireEvent('admin_session_user_login_success', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <adminhtml_customer_save_after>
     */
    public function adminhtmlCustomerSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_save_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <adminhtml_customer_delete_after>
     */
    public function adminhtmlCustomerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <catalog_product_save_after>
     */
    public function catalogProductSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getObject();
        $arguments = array(
            'product' => $this->_getProductArray($product),
        );

        $this->fireEvent('catalog_product_save_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <catalog_product_delete_after>
     */
    public function catalogProductDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getObject();
        $arguments = array(
            'product' => $this->_getProductArray($product),
        );

        $this->fireEvent('catalog_product_delete_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <catalog_category_save_after>
     */
    public function catalogCategorySaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $category = $observer->getEvent()->getObject();
        $arguments = array(
            'category' => $this->_getCategoryArray($category),
        );

        $this->fireEvent('catalog_category_save_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <catalog_category_delete_after>
     */
    public function catalogCategoryDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $category = $observer->getEvent()->getObject();
        $arguments = array(
            'category' => $this->_getCategoryArray($category),
        );

        $this->fireEvent('catalog_category_delete_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <catalog_product_status_update>
     */
    public function catalogProductStatusUpdate($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $product_id = $observer->getEvent()->getProductId();
        $store_id = $observer->getEvent()->getStoreId();
        $arguments = array(
            'product' => $product_id,
            'store_id' => $store_id,
        );

        $this->fireEvent('catalog_product_status_update', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <checkout_cart_add_product_complete>
     */
    public function checkoutCartAddProductComplete($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $request = $observer->getEvent()->getRequest();

        $arguments = array(
            'product' => $this->_getProductArray($product),
            'request' => $request->getParams(),
        );

        $this->fireEvent('checkout_cart_add_product_complete', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <checkout_controller_onepage_save_shipping_method>
     */
    public function checkoutControllerOnepageSaveShippingMethod($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();
        $request = $observer->getEvent()->getRequest();

        $arguments = array(
            'quote' => $this->_getQuoteArray($quote),
            'request' => $request->getParams(),
        );

        $this->fireEvent('checkout_controller_onepage_save_shipping_method', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <checkout_onepage_controller_success_action>
     */
    public function checkoutOnepageControllerSuccessAction($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId(); 
        $order = Mage::getModel('sales/order')->load($orderId);
        $arguments = array(
            'order' => $this->_getOrderArray($order),
        );

        //file_put_contents( '/tmp/magebridge.debug', "checkoutOnepageControllerSuccessAction\r\n", FILE_APPEND );
        //file_put_contents( '/tmp/magebridge.debug', var_export($arguments,true)."\r\n", FILE_APPEND );
        $this->fireEvent('checkout_onepage_controller_success_action', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <checkout_type_onepage_save_order_after>
     */
    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $arguments = array(
            'order' => $this->_getOrderArray($order),
            'quote' => $this->_getQuoteArray($quote),
        );

        $this->fireEvent('checkout_type_onepage_save_order_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <customer_delete_after>
     */
    public function customerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <customer_login>
     */
    public function customerLogin($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_login', $arguments);
        $this->addEvent('magento', 'customer_login_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <customer_logout>
     */
    public function customerLogout($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_logout', $arguments);
        $this->addEvent('magento', 'customer_logout_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <customer_save_after>
     */
    public function customerSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        return $this;
        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => $this->_getCustomerArray($customer),
        );

        $this->fireEvent('customer_save_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <joomla_on_after_delete_user>
     */
    public function joomlaOnAfterDeleteUser($arguments)
    {
        return $this;
    }

    /*
     * Method fired on the event <sales_convert_order_to_quote>
     */
    public function salesConvertOrderToQuote($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        
        $arguments = array(
            'order' => $this->_getOrderArray($order),
            'quote' => $this->_getQuoteArray($quote),
        );

        $this->fireEvent('sales_convert_order_to_quote', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <sales_order_place_after>
     */
    public function salesOrderPlaceAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $arguments = array(
            'order' => $this->_getOrderArray($order),
        );

        $this->fireEvent('sales_order_place_after', $arguments);
        return $this;
    }

    /*
     * Method fired on the event <sales_order_save_after>
     */
    public function salesOrderSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer->getEventName()) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $arguments = array(
            'order' => $this->_getOrderArray($order),
        );

        $this->fireEvent('sales_order_save_after', $arguments);
        return $this;
    }

    /*
     * Method that adds this event to the Joomla! bridge-reply
     */
    public function addEvent($group = null, $event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        if(empty($event)) {
            Mage::getSingleton('magebridge/debug')->notice('Listener: Empty event');
            return false; 
        }

        // Exit if forwarding of this event is disabled
        if($this->isEnabled($event) == false) {
            return false; 
        }

        // Convert the lower-case event-name to camelCase
        $event = $this->_convertEventName($event);

        // Add this event to the response-data
        Mage::getSingleton('magebridge/debug')->notice('Listener: Adding event "'.$event.'" to the response-data');
        Mage::getSingleton('magebridge/session')->addEvent($group, $event, $arguments);
    }

    /*
     * Method that forwards the event to Joomla! straight-away through XML-RPC
     */
    public function fireEvent($event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        if(empty($event)) {
            Mage::getSingleton('magebridge/debug')->notice('Listener: Empty event');
            return false; 
        }

        // Exit if forwarding of this event is disabled
        if($this->isEnabled($event) == false) {
            Mage::getSingleton('magebridge/debug')->notice('Listener: Event "'.$event.'" is disabled');
            return false; 
        }

        // Force the argument as struct
        if(!is_array($arguments)) {
            $arguments = array('null' => 'null');
        }

        Mage::getSingleton('magebridge/debug')->notice('Listener: Forwarding event "'.$event.'" through XML-RPC');

        // Convert the lower-case event-name to camelCase
        $event = $this->_convertEventName($event);

        // Initialize the multi-call array, add the event to it and make the call
        Mage::getSingleton('magebridge/client')->addDebug();
        Mage::getSingleton('magebridge/client')->addCall('magebridge.event', array($event, $arguments));
        Mage::getSingleton('magebridge/client')->multicall();

        // There should be no more debugging after this
        return true;
    }

    /* 
     * Method to check if an event is enabled or not
     */
    public function isEnabled($event)
    {
        $enabled = Mage::getStoreConfig('magebridge/settings/event_forwarding/'.$event);
        if($enabled != null) {
            return (boolean)$enabled;
        }
        return true;
    }

    /*
     * Method to convert an underscore-based event-name to camelcase
     */
    public function _convertEventName($event)
    {
        $event_parts = explode('_', $event);
        $event = 'mage';
        foreach($event_parts as $part) {
            $event .= ucfirst($part);
        }
        return $event;
    }

    /*
     * Method that returns address-data as a basic array
     */
    private function _getAddressArray($address) 
    { 
        if(empty($address)) return;

        // Small hack to make sure we load the English country-name
        Mage::getSingleton('core/locale')->setLocale('en_US');

        $addressArray[] = array_merge($this->_cleanAssoc($address->debug()), $this->_cleanAssoc(array(
            'country' => $address->getCountryModel()->getName(),
            'is_subscribed' => $address->getIsSubscribed(),
        )));
        
        return $addressArray;
    }

    /*
     * Method that returns customer-data as a basic array
     */
    private function _getCustomerArray($customer) 
    { 
        if(empty($customer)) return;

        $addresses = $customer->getAddresses();
        $addressArray = array();
        if(!empty($addresses)) {
            foreach($addresses as $address) {
                $addressArray[] = $this->_getAddressArray($address);
            }
        }

        $customerArray = array_merge($this->_cleanAssoc($customer->debug()), $this->_cleanAssoc(array(
            'customer_id' => $customer->getId(),
            'name' => $customer->getName(),
            'addresses' => $addressArray,
            'session' => Mage::getSingleton('magebridge/core')->getMetaData('joomla_session'),
        )));

        if(!empty($customerArray['password'])) {
            $customerArray['password'] = Mage::getSingleton('magebridge/encryption')->encrypt($customerArray['password']);
        }

        return $customerArray;
    }

    /*
     * Method that returns order-data as a basic array
     */
    private function _getOrderArray($order) 
    { 
        if(empty($order)) return;

        $products = array();
        foreach ($order->getAllItems() as $item) {
            $product = $this->_cleanAssoc(array(
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'product_type' => $item->getProductType(),
            ));
            $products[] = $product;
        }

        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $orderArray = $this->_cleanAssoc($order->debug());
        $orderArray['order_id'] = $order->getId();
        $orderArray['customer'] = $this->_getCustomerArray($customer);
        $orderArray['products'] = $products;

        return $orderArray;
    }

    /*
     * Method that returns quote-data as a basic array
     */
    private function _getQuoteArray($quote) 
    { 
        if(empty($quote)) return;

        $products = array();
        foreach ($quote->getAllItems() as $item) {
            $product = $this->_cleanAssoc(array(
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'product_type' => $item->getProductType(),
            ));
            $products[] = $product;
        }

        $quoteArray = $this->_cleanAssoc(array(
            'quote_id' => $quote->getId(),
            'quote' => $quote->debug(),
            'customer' => $this->_getCustomerArray($quote->getCustomer()),
            'products' => $products,
        ));

        return $quoteArray;
    }

    /*
     * Method that returns user-data as a basic array
     */
    private function _getUserArray($user) 
    { 
        if(empty($user)) return;

        $userArray = $this->_cleanAssoc(array(
            'user_id' => $user->getId(),
            'user' => $user->debug(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ));

        return $userArray;
    }

    /*
     * Method that returns product-data as a basic array
     */
    private function _getProductArray($product) 
    { 
        if(empty($product)) return;

        $productArray = $this->_cleanAssoc(array(
            'product_id' => $product->getId(),
            'sku' => $product->getSKU(),
            'name' => $product->getName(),
            'status' => $product->getStatus(),
            'price' => $product->getFinalPrice(),
            'category_id' => $product->getCategoryId(),
            'category_ids' => $product->getCategoryIds(),
            'product_type' => $product->getProductType(),
            'product_url' => $product->getProductUrl(false),
            'images' => $product->getMediaGallery('images'),
            'debug' => $product->debug(),
        ));

        return $productArray;
    }

    /*
     * Method that returns category-data as a basic array
     */
    private function _getCategoryArray($category) 
    { 
        if(empty($category)) return;

        $categoryArray = $this->_cleanAssoc(array(
            'category_id' => $category->getId(),
            'name' => $category->getName(),
            'debug' => $category->debug(),
        ));

        return $categoryArray;
    }

    /*
     * Helper-method that cleans an associative array to prevent empty values
     */
    private function _cleanAssoc($assoc)
    {
        if(!empty($assoc)) {
            foreach ($assoc as $name => $value) {
                if(empty($value)) unset($assoc[$name]);
            }
        }
        return $assoc;
    }
}
