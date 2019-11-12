<?php

class Model
{

    protected $_prefix = 'goctinmoi.';
    protected $_cache;
    protected $_key = '_KEYS_';
    protected $_cacheActive = true;
    protected $_layoutName;
    protected $_elementName;

    public function __construct($conn = null, $cache = null)
    {
        $this->_conn = $conn === null ? Helper::getInstance()->getDbConnection() : $conn;
        $this->_cache = $cache === null ? Helper::getInstance()->getMemcachedConnection() : $cache;
    }

    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
        return $this;
    }

    public function setConnection($conn)
    {
        $this->_conn = $conn;
        return $this;
    }

    public function setCache($cache)
    {
        $this->_cache = $cache;
        return $this;
    }


    // note
    public function getCacheData($key)
    {
        //return null;
        //$result =  $this->_cacheActive ? unserialize( $this->_cache->get( $this->_prefix. $key )) : null;
//        $result = $this->_cacheActive ? $this->_cache->get($this->_prefix . $key) : null;
        //if ($result === false){
        //echo '<!--'. $key. '-->';
        //}
//        return $result;
    }

    public function setCacheData($key, $data, $ttl = 1000000)
    {
        //return null;
        //$bool = $this->_cache->set( $this->_prefix. $key , serialize( $data ), $ttl);
        //if (!empty($data)){
        return $this->_cache->set($this->_prefix . $key, $data, $ttl);
        //}
        //if ($bool == false) {
        //echo '<!--'. $key. '-->';
        //}
        return null;
    }

    public function __destruct()
    {
        if (is_resource($this->_conn)) {
            $this->_conn->close();
            unset($this->_conn);
        }
    }

    public function setCacheActive($active = true)
    {
        $this->_cacheActive = $active;
        return $this;
    }

    public function setLayoutName($layoutName)
    {
        $this->_layoutName = $layoutName;
        return $this;
    }

    public function setElementName($elementName)
    {
        $this->_elementName = $elementName;
        return $this;
    }

    public function resetLayoutName()
    {
        $this->setLayoutName(null)->setElementName(null);
    }

    public function resetCacheItems($value)
    {
        $this->setCacheData($this->_key, $value);
    }

    public function addCacheItem($modelName, $method, $param = null, $reset = false)
    {
        if ($modelName && $method && $this->_layoutName && $this->_elementName) {
            $result = $this->getCacheData($this->_key);
            $item = array('model' => $modelName, 'method' => $method, 'param' => $param);
            $key = serialize($item);
            if (isset($result[$this->_layoutName][$this->_elementName])) {
                $result[$this->_layoutName][$this->_elementName][$key] = $item;
            } else {
                $result[$this->_layoutName][$this->_elementName] = array($key => $item);
            }
            $this->setCacheData($this->_key, $result);
            if ($reset) {
                $this->resetLayoutName();
            }
        }
        return $this;
    }

    public function getCacheItem($layoutName = null, $elementName = null)
    {
        $result = $this->getCacheData($this->_key);
        if ($result) {
            if (isset($result[$layoutName][$elementName])) {
                return $result[$layoutName][$elementName];
            }
            if (isset($result[$layoutName])) {
                return $result[$layoutName];
            }
            return $result;
        }
        return null;
    }

}
