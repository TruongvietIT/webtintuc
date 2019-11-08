<?php

class Route {

    protected $_rules;
    protected $_params;
    protected $_cachedRules;

    public function __construct() {
        $folderName = Context::getInstance()->getFolderName();
        $this->setRule($folderName);
    }

    public function setRule($item = 'default') {
        $rules = include Context::getInstance()->getBasePath() . 'config/route.php';
        if (isset($rules[$item])) {
            $this->_rules = $rules[$item];
        } else {
            $this->_rules = $rules['default'];
        }
    }

    public function addRule($rule, $append = true) {
        if ($append) {
            array_push($this->_rules, $rule);
        } else {
            array_unshift($this->_rules, $rule);
        }
    }

    public function getParam($key, $default = null) {
        if (!empty($key) && isset($this->_params [$key])) {
            return $this->_params [$key];
        }
        return $default;
    }

    public function parseUrl($url) {
        if (isset($this->_cachedRules [$url])) {
            return $this->_cachedRules [$url];
        }

        foreach ($this->_rules as $rule => $val) {
            if ($rule == $url || trim($rule, '/') == trim($url, '/')) {
                return $this->_cachedRules [$url] = $val;
            }
            $aliasRule = preg_replace('#<\w+:|>#is', '', $rule);
            if (preg_match('#^' . $aliasRule . '$#is', $url)) {
                $rule = preg_replace('#<(\w+):(.*?)>#is', '(?P<$1>$2)', $rule);
                if (preg_match('#' . $rule . '#is', $url, $matches)) {
                    $this->_params = $matches;
                }
                return $this->_cachedRules [$url] = $val;
            }
        }
        return $this->_cachedRules [$url] = $this->_rules ['default'];
    }

    public function createUrl($route, $params = array(), $domain = false) {
        $rule = array_search($route, $this->_rules);
        if (false !== $rule) {
            foreach ($params as $key => $val) {
                $rule = preg_replace('#<' . $key . ':[^>]+>#is', $val, $rule);
            }
            $rule = preg_replace('#\([^\)]*\w+:[^\)]*\)\??|\(\?:|\)\??#s', '', $rule);
            return ($domain == true ? Context::getInstance()->getFront()->getRequest()->getDomain() . '/' : '') . $rule;
        }
        return null;
    }

}
