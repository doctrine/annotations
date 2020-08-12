<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

/*
 * This file bootstraps the test environment.
 */
error_reporting(E_ALL | E_STRICT);

// register silently failing autoloader
spl_autoload_register(static function ($class) {
    if (strpos($class, 'Doctrine\Tests\\') !== 0) {
        return;
    }

    $path = __DIR__ . '/../../' . strtr($class, '\\', '/') . '.php';
    if (is_file($path) && is_readable($path)) {
        require_once $path;

        return true;
    }
});

require_once __DIR__ . '/../../../vendor/autoload.php';

AnnotationRegistry::registerAutoloadNamespace(
    'Doctrine\Tests\Common\Annotations\Fixtures',
    __DIR__ . '/../../'
);
