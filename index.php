<?php

$basePath = str_replace('\\', '/', dirname(__FILE__)) . '/';
include $basePath . 'lib/Context.php';
Context::getInstance()->setBasePath($basePath)->setup()->execute();
?>