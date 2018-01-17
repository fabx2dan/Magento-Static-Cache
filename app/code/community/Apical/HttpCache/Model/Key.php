<?php

class Apical_HttpCache_Model_Key
{
    protected $_key;

    /**
     * 
     * @param type $v
     * @return $this
     */
    public function generate($v) {
        $this->_key = preg_replace( "/[^a-z0-9 ]/i", "", $v);

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