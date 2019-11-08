<?php

class HttpAuth implements AuthInterface {

    protected static $_instance;
    protected $_storage;
    protected $_member = '__MISS__';
    protected $_identity;

    public static function getInstance() {
        if (false === isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setStorage() {
        $this->_storage = new Session();
        $this->_storage->init();
        return $this;
    }

    public function getStorage() {
        if (null === $this->_storage) {
            $this->setStorage();
        }
        return $this->_storage;
    }

    public function isAuthenticated() {
        return null !== $this->getStorage()->get($this->_member);
    }

    public function setIdentity($identity) {
        $this->getStorage()->offsetSet($this->_member, $identity);
    }

    public function getIdentity() {
        if (null === $this->_identity) {
            $this->_identity = $this->getStorage()->get($this->_member);
        }
        return $this->_identity;
    }

    public function setMemeber($member) {
        $this->_member = $member;
        return $this;
    }

    public function getUserName($default = 'Anonymous') {
        if (null === $this->_identity) {
            $this->_identity = $this->getStorage()->get($this->_member);
        }
        if (isset($this->_identity['user_name'])) {
            return $this->_identity['user_name'];
        }
        return $default;
    }

    public function getUserId() {
        if (null === $this->_identity) {
            $this->_identity = $this->getStorage()->get($this->_member);
        }
        if (isset($this->_identity['user_id'])) {
            return $this->_identity['user_id'];
        }
        return null;
    }

    public function authenticate() {
        return $this->getStorage()->get($this->_member);
    }

    public function clear() {
        $this->getStorage()->offsetUnset($this->_member);
    }

}

Interface AuthInterface {

    public function authenticate();
}
