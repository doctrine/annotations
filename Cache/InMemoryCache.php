<?php

namespace Doctrine\Common\Annotations\Cache;

class InMemoryCache implements CacheInterface
{
    private $classAnnotations = array();
    private $propertyAnnotations = array();
    private $methodAnnotations = array();

    public function getClassAnnotationsFromCache(\ReflectionClass $class)
    {
        $key = $class->getName();

        if (isset($this->classAnnotations[$key])) {
            return $this->classAnnotations[$key];
        }

        return null;
    }

    public function getPropertyAnnotationsFromCache(\ReflectionProperty $property)
    {
        $key = $property->getDeclaringClass()->getName().'->'.$property->getName();

        if (isset($this->propertyAnnotations[$key])) {
            return $this->propertyAnnotations[$key];
        }

        return null;
    }

    public function getMethodAnnotationsFromCache(\ReflectionMethod $method)
    {
        $key = $method->getDeclaringClass()->getName().'::'.$method->getName();

        if (isset($this->methodAnnotations[$key])) {
            return $this->methodAnnotations[$key];
        }

        return null;
    }

    public function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations)
    {
        $key = $class->getName();
        $this->classAnnotations[$key] = $annotations;
    }

    public function putPropertyAnnotationsInCache(\ReflectionProperty $property, array $annotations)
    {
        $key = $property->getDeclaringClass()->getName().'->'.$property->getName();
        $this->propertyAnnotations[$key] = $annotations;
    }

    public function putMethodAnnotationsInCache(\ReflectionMethod $method, array $annotations)
    {
        $key = $method->getDeclaringClass()->getName().'::'.$method->getName();
        $this->methodAnnotations[$key] = $annotations;
    }
}