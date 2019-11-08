<?php

class View {

    protected $_templatePath;
    protected $_templateFile;
    protected $_templateExt = '.tpl.php';
    protected $_elementExt = '.php';
    protected $_basePath;
    protected $_elements = array();
    protected $_cache;
    protected $_keyCache;

    public function setBasePath($path) {
        $this->_basePath = $path;
        return $this;
    }

    public function getBasePath() {
        return $this->_basePath;
    }

    public function setTemplatePath($templatePath) {
        $this->_templatePath = $templatePath;
        return $this;
    }

    public function getTemplatePath() {
        return $this->_templatePath;
    }

    public function setTemplateFile($templateFile) {
        $this->_templateFile = $templateFile;
        return $this;
    }

    public function setTemplateExt($ext) {
        $this->_templateExt = $ext;
    }

    public function getTemplateExt() {
        return $this->_templateExt;
    }

    public function assign($key, $value) {
        $this->$key = $value;
        return $this;
    }

    public function assignObject($object) {
        foreach (get_object_vars($object) as $key => $val) {
            $this->$key = $val;
        }
        return $this;
    }

    public function assginArray($array) {
        foreach ($array as $key => $val) {
            $this->$key = $val;
        }
        return $this;
    }

    public function assignRef($key, &$val) {
        $this->$key = & $val;
        return $this;
    }

    // unset var of thist
    public function clearVars() {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if ('_' !== $key [0]) {
                unset($this->$key);
            }
        }
    }

    // Get all var of this
    public function getVars() {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($vars [$key]);
            }
        }
        return $vars;
    }

    public function render($path, $data = null) {
        if (!is_readable($path)) {
            return;
        }
        ob_start();
        include $path;

        return ob_get_clean();
    }

    public function escape($var) {
        return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
    }

    public function registerElement($elementName, $elementPath, $params = null, $cacheConn = null) {

        //$start = microtime(true);
        if (false == isset($this->_elements [$elementName])) {
            if (!class_exists($elementName)) {
                $path = $this->_basePath . $elementPath . $elementName . $this->_elementExt;
                if (file_exists($path)) {
                    include $path;
                } else
                    return;
            }
            $this->_elements [$elementName] = new $elementName ();

            if (null === $cacheConn) {
                $this->_elements [$elementName]->setParams($params);
                $this->_elements [$elementName]->setup();
            } else {
                $keyCache = preg_replace('#[^\w\d]+#i', '', $elementName . $elementPath . (empty($params) ? implode('_', array_keys($params)) : ''));
                $this->_elements [$elementName]->setKeyCache($keyCache);
                $this->_elements [$elementName]->setCache($cacheConn);
                if ($output = $this->_elements [$elementName]->getCacheData()) {
                    $this->_elements [$elementName]->setParams($params);
                    $this->_elements [$elementName]->setup();
                }
            }
        }

        //echo '<!--'. $elementName. ' Loading time: '. (microtime(true) - $start). '-->';
    }

    public function registerSameElement($elementName, $className, $elementPath, $params = null, $cacheConn = null) {


        if (false == isset($this->_elements [$elementName])) {
            if (!class_exists($className)) {
                $path = $this->_basePath . $elementPath . $className . $this->_elementExt;

                if (file_exists($path)) {
                    include $path;
                } else
                    return;
            }
            $this->_elements [$elementName] = new $className ();

            if (null === $cacheConn) {
                $this->_elements [$elementName]->setParams($params);
                $this->_elements [$elementName]->setup();
            } else {
                $keyCache = preg_replace('#[^\w\d]+#i', '', $elementName . $elementPath . (empty($params) ? implode('_', array_keys($params)) : ''));
                $this->_elements [$elementName]->setKeyCache($keyCache);
                $this->_elements [$elementName]->setCache($cacheConn);
                if ($output = $this->_elements [$elementName]->getCacheData()) {
                    $this->_elements [$elementName]->setParams($params);
                    $this->_elements [$elementName]->setup();
                }
            }
        }
    }

    public function renderElement($elementName) {
        if (false === isset($this->_elements [$elementName])) {
            return;
        }

        if (isset($this->_elements [$elementName]->_keyCache)) {
            $output = $this->_elements [$elementName]->getCacheData();
        }
        if (!isset($output)) {
            $output = $this->_elements [$elementName]->load();
            if (isset($this->_elements [$elementName]->_keyCache)) {
                $this->_elements [$elementName]->setCacheData($output);
            }
            unset($this->_elements [$elementName]);
        }
        echo $output;
    }

    public function setCache($cacheConn) {
        $this->_cache = $cacheConn;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function setKeyCache($keyCache) {
        $this->_keyCache = $keyCache;
    }

    public function getKeyCache() {
        return $this->_keyCache;
    }

    public function getCacheData() {
        $this->_cache->get($this->_keyCache);
    }

    public function setCacheData($data) {
        $this->_cache->set($this->_keyCache, $data);
    }

}

?>