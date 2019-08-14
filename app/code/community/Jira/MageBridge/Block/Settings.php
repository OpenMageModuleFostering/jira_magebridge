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

class Jira_MageBridge_Block_Settings extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/settings.phtml');
    }

    /*
     * Helper to return the header of this page
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - '.$this->__($title);
    }

    /*
     * Helper to return the menu
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /*
     * Helper to return the save URL
     */
    public function getSaveUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('magebridge/index/save');
    }

    /*
     * Helper to reset some MageBridge values to their recommended value
     */
    public function getRecommendedUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('magebridge/index/recommended');
    }

    /*
     * Helper to reset some MageBridge values to null
     */
    public function getResetUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('magebridge/index/reset');
    }

    /**
     * Render block HTML
     */
    protected function _toHtml()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')->setId('magebridge');

        $accordion->addItem('joomla', array(
            'title'   => Mage::helper('adminhtml')->__('Joomla! Connection'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_joomla')->toHtml(),
            'open'    => true,
        ));

        $accordion->addItem('events', array(
            'title'   => Mage::helper('adminhtml')->__('Event Forwarding'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_events')->toHtml(),
            'open'    => false,
        ));

        $accordion->addItem('other', array(
            'title'   => Mage::helper('adminhtml')->__('Other Settings'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_other')->toHtml(),
            'open'    => false,
        ));

        $this->setChild('accordion', $accordion);

        $this->setChild('recommended_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Reset to recommended'),
                    'onclick' => 'magebridgeForm.submit(\''.$this->getRecommendedUrl().'\')',
                    'class' => 'delete'
                ))
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Reset to default'),
                    'onclick' => 'magebridgeForm.submit(\''.$this->getResetUrl().'\')',
                    'class' => 'delete'
                ))
        );

        return parent::_toHtml();
    }
}
