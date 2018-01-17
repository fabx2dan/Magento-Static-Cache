<?php

class Apical_HttpCache_HttpCacheController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Flush cache action for Cache Management. 
     */
    public function flushAction() {
        Mage::getModel('Apical_HttpCache_Model_Processor')->flushAll();
        $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("HttpCache has been flushed."));
        $this->_redirect('adminhtml/system_config/edit/section/apical');
    }

}