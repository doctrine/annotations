<?php

namespace Doctrine\Common\Annotations;

use function array_key_exists;
use function class_exists;

final class AnnotationRegistry
{
    /**
     * An array of classes which cannot be found
     *
     * @var null[] indexed by class name
     */
    private static $failedToAutoload = [];

    public static function reset(): void
    {
        self::$failedToAutoload = [];
    }

    /**
     * Autoloads an annotation class silently.
     */
    public static function loadAnnotationClass(string $class): bool
    {
        if (class_exists($class, false)) {
            return true;
        }

        if (array_key_exists($class, self::$failedToAutoload)) {
            return false;
        }

        if (class_exists($class)) {
            return true;
        }

        self::$failedToAutoload[$class] = null;

        return false;
    }
}
