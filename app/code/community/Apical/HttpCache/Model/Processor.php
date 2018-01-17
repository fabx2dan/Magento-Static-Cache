<?php

class Apical_HttpCache_Model_Processor {

    protected $_key;
    protected $_data;
    protected $_cache;
    protected $_subkey;
    protected $_lifetime;

    const LOGFILE = 'http_cache.log';
    const COMPRESSION_LEVEL = 3;

    function __construct() {
        $this->helper = new Apical_HttpCache_Helper_Data();
        
        if($this->helper->isEnabled()) {
            // Main cache key
            $this->cacheKey = new Apical_HttpCache_Model_Key();
            $this->cacheKey->generate($this->helper->getUri());

            // Secodary cache key for specific content
            $this->cacheSubKey = new Apical_HttpCache_Model_SubKey();
            $this->cacheSubKey->generate();

            $this->initCache();
        }
    }

    /**
     * Get data.
     * 
     * @return type
     */
    protected function getData($k = null) {
        return is_null($k) ? $this->_data[$k] : $this->_data;
    }
    
    /**
     * Set data.
     * 
     * @param type $v
     * @return $this
     */
    protected function setData($v) {
        $this->_data = $v;
        
        return $this;
    }

     /**
     * Initializes Zend cache.
     * 
     * @return boolean
     */
    protected function initCache() {
        try {
            $io = new Varien_Io_File();
            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'http_cache');
        } catch (Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        try {
            /** @var Zend_Cache_Backend_File _cache */
            $this->_cache = Zend_Cache::factory('Core',
                'File',
                array(
                    'lifetime' => null,
                    'automatic_serialization' => true
                ),
                array(
                    'cache_dir' => Mage::getBaseDir('var') . DS . 'http_cache'
                )
            );
        } catch(Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        return true;
    }

    /**
     * Saves cache to filesystem.
     * 
     * @param type $data
     * @return boolean
     */
    protected function saveCache($data) {

        try {
            $_data = $this->loadContent();
            
            switch(true) {
                case (!$_data):
                    $_data = array();
                    break;
                
                case (isset($_data[$this->cacheSubKey->get()])):
                    return true;
                
                default:
                    $this->_cache->remove($this->cacheKey->get());
                
            }

            if(extension_loaded('zlib')) {
                $data = gzcompress($data, self::COMPRESSION_LEVEL);
            }

            $_data[$this->cacheSubKey->get()] = array(
                'ts' => time(),
                'data' => $data
            );

            $this->_cache->save($_data, $this->cacheKey->get());
            
        } catch(Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        return true;
    }

    /**
     * Flushes entire cache.
     * 
     * @return boolean
     */
    protected function flushCache() {

        try {
            $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        } catch(Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        return true;
    }

    /**
     * Flushes a specific subkey.
     * 
     * @return boolean
     */
    protected function flushSubkey() {

        try {
            $_data = $this->loadContent();
            unset($_data[$this->cacheSubKey->get()]);
            
            $this->setData($_data);
            
            $this->_cache->remove($this->cacheKey->get());
            $this->_cache->save($this->getData(), $this->cacheKey->get());
        } catch(Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        return true;
    }

    /**
     * Extracts cache from filesystem.
     * 
     * @return boolean
     */
    protected function loadCache() {

        try {
            $this->setData($this->_cache->load($this->cacheKey->get()));
        } catch(Exception $x) {
            Mage::log('HTTP_CACHE exception: '.$x->getMessage(), null, self::LOGFILE);
            return false;
        }

        return $this->getData();
    }

    /**
     * Searches for specific key, if any. Otherwise entire cache is returned.
     * 
     * @param type $search
     * @return boolean
     */
    protected function loadContent($search = null) {

        // Initializes cache
        $this->loadCache();

        if(!isset($search))
        {
            return $this->getData();
        }
        
        $search = preg_replace( "/[^a-z0-9 ]/i", "", $search);
        
        $cacheObj = $this->getData($search);

        if($cacheObj && $this->helper->isExpired($cacheObj)) {
            $this->flushSubkey();
            return false;
        } else {
            return extension_loaded('zlib') 
                ? gzuncompress($cacheObj['data']) 
                : $cacheObj['data'];
        }
        
    }

    /**
     * * Magento entry point to check for cached content.
     * 
     * @return boolean
     */
    public function extractContent() {

        if($this->helper->isEnabled() 
            && $this->helper->isAllowed()
            && isset($this->_cache)
            && $this->helper->isCacheable($this->helper->getUri())
        ) {
            $content = $this->loadContent($this->cacheSubKey->get());
        
            if($content) {
                $contentType = Mage::app()->getRequest()->getHeader('Accept');
                Mage::app()->getResponse()->setHeader('Content-Type', $contentType);

                return $content;
            }
        }

        return false;
    }

    /**
     * Saves cache content.
     * 
     * @param type $content
     * @return $this
     */
    public function saveContent($content) {

        $this->saveCache($content);

        return $this;
    }

    /**
     * Flushes everything.
     * 
     * @return $this
     */
    public function flushAll() {

        $this->flushCache();

        return $this;
    }

}
