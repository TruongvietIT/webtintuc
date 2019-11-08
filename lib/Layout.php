<?php

class Layout extends View {

    protected $_pageTitle = 'Untitled';
    protected $_pageDescription = '';
    protected $_pageKeywords = '';
    protected $_css = array();
    protected $_scripts = array();
    protected $_layoutPath;
    protected $_layoutExt = '.php';
    protected $_elements = array();
    protected $_isRender = true;

    public function __construct($basePath = null, $templatePath = null) {
        $this->setBasePath($basePath);
        $this->setTemplatePath($templatePath);
    }

    public function setPageTitle($title) {

        $this->_pageTitle = $title;
        return $this;
    }

    public function getPageTitle() {
        return strip_tags($this->_pageTitle);
    }

    public function setPageDescription($desc) {
        $this->_pageDescription = $desc;
        return $this;
    }

    public function getPageDescription() {
        return htmlspecialchars($this->_pageDescription, ENT_QUOTES);
    }

    public function setPageKeywords($keywords) {
        $this->_pageKeywords = str_replace('"', '', $keywords);
        return $this;
    }

    public function getPageKeywords() {
        return $this->_pageKeywords;
    }

    public function addCss($path) {
        $this->_css[] = $path;
    }

    public function appendCss() {
        foreach ($this->_css as $path) {
            echo '<link href="' . $path . '" type="text/css" rel="stylesheet" />';
        }
    }

    public function addScript($path) {
        $this->_scripts[] = $path;
    }

    public function appendScripts() {
        foreach ($this->_scripts as $path) {
            echo '<script src="' . $path . '" type="text/javascript"></script>';
        }
    }

    public function setLayoutName($layoutName) {
        $this->_layoutName;
    }

    public function setLayoutPath($path) {
        $this->_layoutPath = $path;
    }

    public function getLayoutPath() {
        return $this->_layoutPath;
    }

    public function setLayoutExt($ext) {
        $this->_layoutExt = $ext;
    }

    public function getLayoutExt() {
        return $this->_layoutExt;
    }

    public function setRender($bool = true) {
        $this->_isRender = $bool === true;
    }

    public function load() {
        if ($this->_isRender) {
            $filePath = $this->_basePath .
                    $this->_templatePath .
                    $this->_templateFile .
                    $this->_templateExt;

            return $this->render($filePath);
        }
    }

}

class Element extends View {

    protected $_elementPath;
    protected $_params;

    public function setElementPath($path) {
        $this->_elementPath = $path;
    }

    public function getElementPath() {
        return $this->_elementPath;
    }

    public function setParams($params) {
        $this->_params = $params;
    }

    public function getParams() {
        return $this->_params;
    }

    public function setElementExt($ext) {
        $this->_elementExt = $ext;
    }

    public function getElementExt() {
        return $this->_elementExt;
    }

    public function load() {
        $filePath = $this->_basePath .
                $this->_templatePath .
                $this->_templateFile .
                $this->_templateExt;

        return $this->render($filePath);
    }

}

?>
