<?php

class Apical_HttpCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LIFETIME_DEFAULT = 86400; // e.g. 1 day
    const XML_HTTPCACHE_CACHEABLE_URI = 'apical_httpcache/httpcache/cacheable_uri';
    const XML_HTTPCACHE_LIFETIME = 'apical_httpcache/httpcache/cache_lifetime';

    protected $_lifetime;
    protected $_cacheableUri;
    protected $_sanitizedUri;
    protected $_notAllowed = array('/index.php/admin', 'admin'); // url which we'll never cache

    /**
     * Simple URI sanitize.
     *
     * @param $v
     * @return string
     */
    public function sanitizeUri($v)
    {
        if($p = strpos($v, '?')) {
            $v = substr($v, 0, $p);
        }

        if(strlen($v) > 0 && $v[strlen($v) - 1] === '/') {
            $v = substr($v, 0, -1);
        }

        if(strlen($v) > 0 && $v[0] !== '/') {
            $v = '/'.$v;
        }

        return $v;
    }

    /**
     * Get config from database directly, as we don't have Mage app.
     *
     * @param $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        $sql = "SELECT * FROM core_config_data WHERE path='{$path}' AND scope='default'";
        $row = Mage::getSingleton('core/resource')
            ->getConnection('core_read')
            ->fetchRow($sql);
        return $row['value'];
    }

    /**
     * Check if module is enabled.
     *
     * @return int
     */
    public function isEnabled()
    {
        // Direct query must be used, no model is available.
        $sql = "SELECT * FROM `core_cache_option` WHERE code = 'apical_httpcache'";
        $row = Mage::getSingleton('core/resource')
            ->getConnection('core_read')
            ->fetchRow($sql);

        return isset($row['value']) ? $row['value'] : 0;
    }
    
    /** 
     * Check if cache subkey has expired.
     * 
     * @param type $val
     */
    public function isExpired($val) {
        return (bool)(time() - $val['ts'] < $this->helper->getLifetime());
    }

    /**
     * Get cacheable URIs from database.
     *
     * @return array
     */
    public function getCacheableUri() {

        $this->_cacheableUri = array_map(function($v) {
            $v = trim($v);

            if($v[strlen($v) - 1] === '/') {
                $v = substr($v, 0, -1);
            }
            
            if($v[0] !== '/') {
                $v = '/'.$v;
            }
            
            return $v;
        }, explode(PHP_EOL,$this->getConfig(self::XML_HTTPCACHE_CACHEABLE_URI)));

        return $this->_cacheableUri;
    }

    /**
     * Get cache's lifetime.
     *
     * @return int
     */
    public function getLifetime() {

        $v = (int)$this->getConfig(self::XML_HTTPCACHE_LIFETIME);

        if(!$v || $v <= 0) {
            $this->_lifetime = self::LIFETIME_DEFAULT;
        }

        return $this->_lifetime;
    }

    /**
     * Check if current URI is cacheable.
     *
     * @param $v
     * @return bool
     */
    public function isCacheable($v) {

        return in_array($this->sanitizeUri($v), $this->getCacheableUri());
    }

    /**
     * Sanitize URI and return it.
     *
     * @return string
     */
    public function getUri()
    {
        if(isset($this->_sanitizedUri)) {
            return $this->_sanitizedUri;
        }

        // PHP global vars must be used, because no Mage::app() is available.
        $this->_sanitizedUri = $this->sanitizeUri($_SERVER['REQUEST_URI']);

        return $this->_sanitizedUri;
    }

    /**
     * Check if current URI is allowed.
     *
     * @return bool
     */
    public function isAllowed()
    {
        foreach ($this->_notAllowed as $notAllowed) {
            if (stripos($notAllowed, $this->getUri()) !== false) {
                $this->_isAllowed = false;
                return false;
            }
        }

        return true;
    }

}