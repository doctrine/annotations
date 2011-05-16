<?php

namespace Doctrine\Common\Annotations\Cache;

interface CacheInterface
{
    /**
     * Returns the class annotations
     *
     * @param ReflectionClass $class
     * @return array|null
     */
    function getClassAnnotationsFromCache(\ReflectionClass $class);

    /**
     * Returns the property annotations
     *
     * @param ReflectionProperty $property
     * @return array|null
     */
    function getPropertyAnnotationsFromCache(\ReflectionProperty $property);

    /**
     * Returns the method annotations
     *
     * @param ReflectionMethod $method
     * @return array|null
     */
    function getMethodAnnotationsFromCache(\ReflectionMethod $method);

    function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations);
    function putPropertyAnnotationsInCache(\ReflectionProperty $class, array $annotations);
    function putMethodAnnotationsInCache(\ReflectionMethod $class, array $annotations);
}