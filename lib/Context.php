<?php

class Context {

    protected static $_instance;
    protected $_front; // controler
    protected $_basePath;
    protected $_route;
    protected $_cachedParams;
    protected $_session;
    private $__cacheActive = false;

    public static function getInstance() {
        if (false === isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getFront() {
        return $this->_front;
    }

    public function getRoute() {
        return $this->_route;
    }

    public function setBasePath($basePath) {
        $this->_basePath = $basePath;
        return $this;
    }

    public function getSession() {
        if (!isset($this->_session)) {
            $this->_session = new Session();
            $this->_session->init();
        }
        return $this->_session;
    }

    public function getBasePath() {
        return $this->_basePath;
    }

    public function addCachedParam($key, $val) {
        $this->_cachedParams[$key] = $val;
    }

    public function getCachedParam($key) {
        if (isset($this->_cachedParams[$key])) {
            return $this->_cachedParams[$key];
        }
        return null;
    }

    public function removeCachedParam($key = null) {
        if ($key !== null) {
            unset($this->_cachedParams[$key]);
        } else {
            unset($this->_cachedParams);
        }
    }

    public function setCacheActive($active = true) {
        $this->__cacheActive = $active;
        return $this;
    }

    public function getCacheActive() {
        return $this->__cacheActive;
    }

    public function getKeyCache($uri, $prefix = 'Page.') {
        $keyCache = preg_replace('#[\?\#].*?|[^\w\d]+#is', '', $uri);
        return $prefix . $keyCache;
    }

    public function load($paths) {
        if (is_string($paths))
            include $this->_basePath . $paths;
        else if (is_array($paths)) {
            foreach ($paths as $path) {
                include $this->_basePath . $path;
            }
        }
    }

    public function setup() {
        //error_reporting(E_ALL);
        ini_set('error_log', $this->_basePath . '/log/error.' . date('Y-m-d') . '.log');
        ini_set('log_errors', 1);
        ini_set('track_errors', 1);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        return $this;
    }

    public function getFolderName($default = 'web') {
        if (isset($this->_folderName)) {
            return $this->_folderName;
        } else {

            $map = array('m.goctinmoi.com' => 'mobile',
                'm.goctinmoi.local' => 'mobile',
                'goctinmoi.com' => 'web',
                'goctinmoi.local' => 'web'
            );

            $domain = $this->getFront()->getRequest()->getHttpHost();

            if (isset($map[$domain])) {
                return $this->_folderName = $map[$domain];
            }
        }
        return $this->_folderName = $default;
    }

    public function start() {
        $this->load(array('lib/Helper.php',
            'lib/cache/MemCached.php',
            'lib/Request.php'));

        $request = new Request();
        $url = $request->getFullUrl();
        $keyCache = $this->getKeyCache($url);

        if ($this->__cacheActive == true) {
            if ($output = Helper::getInstance()->getMemcachedConnection()->get($keyCache)) {
                echo $output . '<!--CACHED-->';
                return;
            }
        }

        $this->load(array('lib/database/Mysql.php',
            'lib/Response.php',
            'lib/Session.php',
            'lib/Controller.php',
            'lib/Auth.php',
            'lib/Acl.php',
            'lib/View.php',
            'lib/Layout.php',
            'lib/Transfer.php',
            'lib/Route.php',
            'lib/Util.php',
            'controls/Front.php',
            'models/Model.php'));

        $this->_front = new Front($request, new Response(), $this->_basePath);
        unset($request, $url, $keyCache);
        $this->_route = new Route();
        $this->_front->init();
    }

    public function execute($debug = false) {
        if ($debug) {
            $this->setup();
        }
        $this->start();
    }

}

?>