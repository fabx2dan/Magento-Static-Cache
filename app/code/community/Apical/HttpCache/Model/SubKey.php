<?php

class Apical_HttpCache_Model_SubKey
{
    protected $_key;

    /**
     * 
     * @return $this
     */
    public function generate() {
        // Global vars have to be used.
        $_getVars = $_GET;
        $_postVars = $_POST;
        
        unset($_getVars['form_key'], $_postVars['form_key']);
        
        $this->_key = md5(json_encode($_getVars + $_postVars).$_SERVER['HTTP_HOST']);

        return $this;
    }

    /**
     * 
     * @return type
     */
    public function get() {
        return $this->_key;
    }

}
