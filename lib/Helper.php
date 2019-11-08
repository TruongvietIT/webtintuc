<?php

class Helper {

    protected static $_instance;
    protected $_dbConfig;
    protected $_dbConn;
    protected $_memConfig;
    protected $_memConn;
    protected $_memdConfig;
    protected $_memdConn;
    protected $_mongoConfig;
    protected $_mongoConn;
    protected $_configs;
    protected $_basePath;

    public function __construct() {
        $this->_basePath = str_replace('\\', '/', dirname(__FILE__)) . '/../';
    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getDbConfig($section) {

        if (true === isset($this->_dbConfig[$section])) {
            return $this->_dbConfig[$section];
        }

        $this->_dbConfig = include $this->_basePath . 'config/mysql.php';

        return $this->_dbConfig[$section];
    }

    public function getDbConnection($section = 'local') {

        if (false === isset($this->_dbConn[$section])) {
            $this->_dbConn[$section] = new Mysql($this->getDbConfig($section));
        }
        return $this->_dbConn[$section];
    }

    public function getMongoConfig($section = 'local') {

        if (true === isset($this->_mongoConfig[$section])) {
            return $this->_mongoConfig[$section];
        }

        $this->_mongoConfig = include $this->_basePath . 'config/mongo.php';

        return $this->_mongoConfig[$section];
    }

    public function getMongoConnection($section = 'local') {
        if (false === isset($this->_mongoConn[$section])) {
            $this->_mongoConn[$section] = new Nosql_Mongo($this->getMongoConfig($section));
        }

        return $this->_mongoConn[$section];
    }

    public function getMemcacheConfig() {
        if (false === isset($this->_memConfig)) {
            $this->_memConfig = include $this->_basePath . 'config/memcache.php';
            ;
        }

        return $this->_memConfig;
    }

    public function getMemcacheConnection() {
        if (false === isset($this->_memConn)) {
            $this->_memConn = new MCache($this->getMemcacheConfig());
        }
        return $this->_memConn;
    }

    public function getMemcachedConfig() {
        if (false === isset($this->_memdConfig)) {
            $this->_memdConfig = include $this->_basePath . 'config/memcached.php';
        }
        return $this->_memdConfig;
    }

    public function getMemcachedConnection() {
        if (false === isset($this->_memdConn)) {
            $this->_memdConn = new MCached($this->getMemcachedConfig());
        }
        return $this->_memdConn;
    }

    public function getConfig($key = null, $default = null) {
        if (!isset($this->_configs)) {
            $this->_configs = include $this->_basePath . 'config/config.php';
        }

        if (null !== $key) {
            if (isset($this->_configs[$key])) {
                return $this->_configs[$key];
            }
            return $default;
        }
        return $this->_configs;
    }

}

?>
