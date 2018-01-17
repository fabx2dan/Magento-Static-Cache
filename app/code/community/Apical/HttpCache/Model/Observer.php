<?php

class Apical_HttpCache_Model_Observer
{

    /**
     * Check if request is cacheable.
     */
    public function isCachable() {
        $helper = new Apical_HttpCache_Helper_Data();

        /** @var Apical_HttpCache_Model_Processor $processor */
        $processor = Mage::getModel('Apical_HttpCache_Model_Processor');

        if($helper->isCacheable($_SERVER['REQUEST_URI'])) {
            $processor->saveContent(Mage::app()->getResponse()->getBody());
        }

    }

    /**
     * Cleans cache type.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function cleanCacheType(Varien_Event_Observer $observer) {
        /** @var Apical_HttpCache_Model_Processor $processor */
        $processor = Mage::getModel('Apical_HttpCache_Model_Processor');

        if ($observer->getData('type') == "apical_httpcache"){
            $processor->flushAll();
        }
    }

}