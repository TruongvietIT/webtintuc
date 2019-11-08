<?php
class MCached
{
    private $_cache   	= null;
    private $_prefix	= '__';
    
    private $_cacheDir 	= null;
    private $_isCache   = false;
    
    const DEFAULT_HOST 				= '127.0.0.1';
    const DEFAULT_PORT 				= 11211;
    const DEFAULT_TIMEOUT 			= 1;
    const DEFAULT_RETRY_INTERVAL 	= 15;
    const DEFAULT_FAILURE_LIMIT		= 3;

    public function __construct($servers)
    {
    	$this->_cacheDir = dirname(__FILE__). '/../../cache/'. date('Y-m-d'). '/';
    	
    	$this->getMemCached();
        /*
        $this->_cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        $this->_cache->setOption(Memcached::OPT_DISTRIBUTION, true);
        $this->_cache->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->_cache->setOption(Memcached::OPT_CONNECT_TIMEOUT, self::DEFAULT_TIMEOUT);
        $this->_cache->setOption(Memcached::OPT_RETRY_TIMEOUT, self::DEFAULT_RETRY_INTERVAL);
        $this->_cache->setOption(Memcached::OPT_SEND_TIMEOUT, self::DEFAULT_TIMEOUT);
        $this->_cache->setOption(Memcached::OPT_RECV_TIMEOUT, self::DEFAULT_TIMEOUT);
        $this->_cache->setOption(Memcached::OPT_POLL_TIMEOUT, self::DEFAULT_TIMEOUT);
        $this->_cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, self::DEFAULT_FAILURE_LIMIT);*/
        //$this->_cache->setOption(Memcached::OPT_COMPRESSION, true);
        //$this->_cache->setOption(Memcached::OPT_TCP_NODELAY, true);

        if ($this->_isCache) {
            $this->_cache->addServers($servers);
        }
    }

    protected function getMemCached()
    {
        if ($this->_isCache) {
            if (false === isset($this->_cache)) {
                $this->_cache = new Memcached();
            }
            return $this->_cache;
        } else {
            return null;
        }
    }
    
    public function get($key)
    {
        if ($this->_isCache) {
            $key = md5($this->_prefix . $key);
            $result = $this->_cache->get($key);
            return $result;
        } else {
            return null;
        }
    }

    public function set($key, $data, $ttl = 120)
    {
        if ($this->_isCache) {
            $key = md5($this->_prefix . $key);
            $result = $this->_cache->set($key, $data, $ttl > 0 ? $ttl : 0);
            return $result;
        } else {
            return null;
        }
    }
    
    public function getMulti($keys)
    {
    	foreach ($keys as &$key){
    		$key = $this->_prefix . $key;
    	}
    	return $this->_cache->getMulti($keys);
    }
    
    public function setMulti($data, $ttl = 120)
    {
    	$temp = array();
    	foreach ($data as $key => $val){
    		$temp[$this->_prefix. $key] = $val;
    	}
    	return $this->_cache->setMulti($temp, $ttl > 0 ?  $ttl += time() : 0);
    }

    public function add($key, $data, $ttl = 120)
    {
        return $this->_cache->add($this->_prefix. $key, $data, $ttl > 0 ?  $ttl += time() : 0);
    }

    public function remove($key)
    {
        return $this->_cache->delete($this->_prefix. $key);
    }

    public function flush()
    {
        return $this->_cache->flush();
    }
    
    public function __destruct()
    {
    	//$this->_cache->quit();
    }
}
