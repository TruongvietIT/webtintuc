<?php

class MCache
{
    /**
     * Memcache instance
     * @var Memcache
     */
    private $_cache   = null;

    /**
     * List of server options
     * @var array
     */
    
    const DEFAULT_HOST 				= '127.0.0.1';
    const DEFAULT_PORT 				= 11211;
    const DEFAULT_PERSISTENT 		= true;
    const DEFAULT_WEIGHT  			= 1;
    const DEFAULT_TIMEOUT 			= 1;
    const DEFAULT_RETRY_INTERVAL 	= 15;
    const DEFAULT_STATUS 			= true;
    const DEFAULT_FAILURE_CALLBACK 	= null;
    
    protected $_options = array('servers' 		=> array(),
						        'compatibility' => false);
    
	public function setOption($name, $value)
    {
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
    }
    
    public function getOption($name){
    	return $this->_options[$name];
    }
    
    public function __construct($options)
    {
    	$this->setOption('servers', $options);
	
        foreach ($this->_options['servers'] as $server) 
        {
        	if (!array_key_exists('host', $server)) {
                $server['host'] = self::DEFAULT_HOST;
            }
            if (!array_key_exists('port', $server)) {
                $server['port'] = self::DEFAULT_PORT;
            }
            if (!array_key_exists('persistent', $server)) {
                $server['persistent'] = self::DEFAULT_PERSISTENT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = self::DEFAULT_WEIGHT;
            }
            if (!array_key_exists('timeout', $server)) {
                $server['timeout'] = self::DEFAULT_TIMEOUT;
            }
            if (!array_key_exists('retry_interval', $server)) {
                $server['retry_interval'] = self::DEFAULT_RETRY_INTERVAL;
            }
            if (!array_key_exists('status', $server)) {
                $server['status'] = self::DEFAULT_STATUS;
            }
            if (!array_key_exists('failure_callback', $server)) {
                $server['failure_callback'] = self::DEFAULT_FAILURE_CALLBACK;
            }
            if ($this->_options['compatibility']) {

            	$this->getMemCache()->addServer($server['host'], $server['port'], $server['persistent'],
                                        		$server['weight'], $server['timeout'],
                                        		$server['retry_interval']);
			} else {
				$this->getMemCache()->addServer($server['host'], $server['port'], $server['persistent'],
                                        		$server['weight'], $server['timeout'],
                                        		$server['retry_interval'],
                                        		$server['status'], $server['failure_callback']);
			}
        }
		
    }

    /**
     * Instantiates an object of Memcache
     * @return Memcache
     */
    protected function getMemCache()
    {
        if (false === isset($this->_cache))
        {
             $this->_cache = new Memcache();
        }
        return $this->_cache;
    }

    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a unique key identifying the cached value
     * @return string the value stored in cache, false if the value is not in the cache or expired.
     */
    public function get($key)
    {
        return $this->_cache->get($key);
    }

    /**
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $data the value to be cached
     * @param int    $ttl the number of seconds in which the cached value will expire.
     *               0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    public function set($key, $data, $ttl = 120)
    {
        if ($ttl > 0){
            $ttl += time();
        }else{
            $ttl = 0;
        }
        return $this->_cache->set($key, $data, MEMCACHE_COMPRESSED, $ttl);
    
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.

     * @param string $key the key identifying the value to be cached
     * @param string $data the value to be cached
     * @param int    $ttl the number of seconds in which the cached value will expire.
     *               0 means never expire.
     * @return bool  true if the value is successfully stored into cache, false otherwise
     */
    public function add($key, $data, $ttl = 120)
    {
        if ($ttl > 0){
            $ttl += time();
        }else{
            $ttl = 0;
        }

        return $this->_cache->add($key, $data, MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Removes a value with the specified key from cache
     *
     * @param string $key the key of the value to be deleted
     * @return bool if no error happens during deletion
     */
    public function remove($key)
    {
        return $this->_cache->delete($key);
    }

   

    /**
     * Deletes all values from cache.
     *
     * Be careful of performing this operation if the cache is shared by multiple applications.
     */
    public function flush()
    {
        return $this->_cache->flush();
    }
}

?>