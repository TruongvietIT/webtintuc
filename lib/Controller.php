<?php

class Controller {

    protected $_request;
    protected $_response;
    protected $_layout;
    protected $_cache;
    protected $_basePath;
    protected $_models;

    public function __construct(Request $request, Response $response, $basePath = null) {
        $this->setRequest($request)
                ->setResponse($response)
                ->setBasePath($basePath);
    }

    public function getRequest() {
        return $this->_request;
    }

    public function setRequest($request) {
        $this->_request = $request;
        return $this;
    }

    public function getResponse() {
        return $this->_response;
    }

    public function setResponse($response) {
        $this->_response = $response;
        return $this;
    }

    public function setLayout(Layout $layout) {
        $this->_layout = $layout;
        return $this;
    }

    public function getLayout() {
        return $this->_layout;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function setCache($cache) {
        $this->_cache = $cache;
        return $this;
    }

    public function setBasePath($path) {
        $this->_basePath = $path;
    }

    public function registerModel($modelName, $modelPath = 'models/', $ext = '.php') {
        if (isset($this->_models[$modelName])) {
            return;
        }
        $filePath = $this->_basePath . $modelPath . $modelName . $ext;
        if (false === is_readable($filePath)) {
            throw new Exception('Cannot load model: ' . $modelName);
        }
        include_once $filePath;
        $this->_models[$modelName] = new $modelName();
    }

    public function getModel($modelName, $modelPath = 'models/', $ext = '.php') {
        if (isset($this->_models[$modelName])) {
            return $this->_models[$modelName];
        } else {
            $this->registerModel($modelName, $modelPath, $ext);
            return $this->_models[$modelName];
        }
    }

    public function registerLayout($layoutName, $layoutPath = 'view/layouts/', $ext = '.php') {
        include $this->_basePath . $layoutPath . $layoutName . $ext;
        $this->setLayout(new $layoutName());
        $this->_layout->setBasePath($this->_basePath);
        $this->_layout->setup();
    }

    public function __destruct() {
        unset($this->_models);
    }

}

?>
