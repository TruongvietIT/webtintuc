<?php

class Response {

    protected $_body = array();
    protected $_headers = array();
    protected $_headersRaw = array();
    protected $_httpResponseCode = 200;
    protected $_isRedirect = false;

    protected function _normalizeHeader($name) {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    public function setHeader($name, $value, $replace = false) {
        $this->canSendHeaders(true);
        $name = $this->_normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = array(
            'name' => $name,
            'value' => $value,
            'replace' => $replace
        );

        return $this;
    }

    public function setRedirect($url, $code = 302) {
        $this->canSendHeaders(true);
        $this->setHeader('Location', $url, true)
                ->setHttpResponseCode($code);
        return $this;
    }

    public function isRedirect() {
        return $this->_isRedirect;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function clearHeaders() {
        $this->_headers = array();
        return $this;
    }

    public function setRawHeader($value) {
        $this->canSendHeaders(true);
        if ('Location' == substr($value, 0, 8)) {
            $this->_isRedirect = true;
        }
        $this->_headersRaw[] = (string) $value;
        return $this;
    }

    public function getRawHeaders() {
        return $this->_headersRaw;
    }

    public function clearRawHeaders() {
        $this->_headersRaw = array();
        return $this;
    }

    public function clearAllHeaders() {
        return $this->clearHeaders()
                        ->clearRawHeaders();
    }

    public function setHttpResponseCode($code) {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {

            throw new Exception('Invalid HTTP response code');
        }

        if ((300 <= $code) && (307 >= $code)) {
            $this->_isRedirect = true;
        } else {
            $this->_isRedirect = false;
        }

        $this->_httpResponseCode = $code;
        return $this;
    }

    public function getHttpResponseCode() {
        return $this->_httpResponseCode;
    }

    public function canSendHeaders($throw = false) {
        $ok = headers_sent($file, $line);
        if ($ok && $throw) {

            throw new Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }
        return !$ok;
    }

    public function sendHeaders() {

        if (count($this->_headersRaw) || count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->_httpResponseCode) {
            return $this;
        }

        $httpCodeSent = false;

        foreach ($this->_headersRaw as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header, true, $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header);
            }
        }

        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }

        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }

        return $this;
    }

    public function setBody($content, $name = null) {
        if ((null === $name) || !is_string($name)) {
            $this->_body = array('default' => (string) $content);
        } else {
            $this->_body[$name] = (string) $content;
        }
        return $this;
    }

    public function appendBody($content, $name = null) {
        if ((null === $name) || !is_string($name)) {
            if (isset($this->_body['default'])) {
                $this->_body['default'] .= (string) $content;
            } else {
                return $this->append('default', $content);
            }
        } elseif (isset($this->_body[$name])) {
            $this->_body[$name] .= (string) $content;
        } else {
            return $this->append($name, $content);
        }
        return $this;
    }

    public function clearBody($name = null) {
        if (null !== $name) {
            $name = (string) $name;
            if (isset($this->_body[$name])) {
                unset($this->_body[$name]);
                return true;
            }

            return false;
        }

        $this->_body = array();
        return true;
    }

    public function getBody($spec = false) {
        if (false === $spec) {
            ob_start();
            $this->outputBody();
            return ob_get_clean();
        } elseif (true === $spec) {
            return $this->_body;
        } elseif (is_string($spec) && isset($this->_body[$spec])) {
            return $this->_body[$spec];
        }

        return null;
    }

    public function append($name, $content) {
        if (!is_string($name)) {

            throw new Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }
        $this->_body[$name] = (string) $content;
        return $this;
    }

    public function prepend($name, $content) {
        if (!is_string($name)) {
            throw new Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        $new = array($name => (string) $content);
        $this->_body = $new + $this->_body;

        return $this;
    }

    public function insert($name, $content, $parent = null, $before = false) {
        if (!is_string($name)) {
            throw new Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if ((null !== $parent) && !is_string($parent)) {
            throw new Exception('Invalid body segment parent key ("' . gettype($parent) . '")');
        }

        if (isset($this->_body[$name])) {
            unset($this->_body[$name]);
        }

        if ((null === $parent) || !isset($this->_body[$parent])) {
            return $this->append($name, $content);
        }

        $ins = array($name => (string) $content);
        $keys = array_keys($this->_body);
        $loc = array_search($parent, $keys);
        if (!$before) {
            // Increment location if not inserting before
            ++$loc;
        }

        if (0 === $loc) {
            // If location of key is 0, we're prepending
            $this->_body = $ins + $this->_body;
        } elseif ($loc >= (count($this->_body))) {
            // If location of key is maximal, we're appending
            $this->_body = $this->_body + $ins;
        } else {
            // Otherwise, insert at location specified
            $pre = array_slice($this->_body, 0, $loc);
            $post = array_slice($this->_body, $loc);
            $this->_body = $pre + $ins + $post;
        }

        return $this;
    }

    public function outputBody() {
        $body = implode('', $this->_body);
        echo $body;
    }

    public function sendResponse() {
        $this->sendHeaders();
        $this->outputBody();
    }

    public function __toString() {
        ob_start();
        $this->sendResponse();
        return ob_get_clean();
    }

}
