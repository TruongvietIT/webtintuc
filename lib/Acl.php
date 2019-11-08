<?php

class Acl {

    protected static $_instance;

    public static function getInstance() {
        if (false === isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function isAllowed($userId, $route) {
        $model = Context::getInstance()->getFront()->getModel('RouteModel');
        return $model->isAllow($userId, $route);
    }

}
