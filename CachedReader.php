<?php

namespace Doctrine\Common\Annotations;

use Doctrine\Common\Annotations\Cache\CacheInterface;

class CachedReader implements ReaderInterface
{
    private $delegate;
    private $cache;

    public function __construct(ReaderInterface $reader, CacheInterface $cache)
    {
        $this->delegate = $reader;
        $this->cache = $cache;
    }

    public function getClassAnnotations(\ReflectionClass $class)
    {
        if (null !== $annots = $this->cache->getClassAnnotationsFromCache($class)) {
            return $annots;
        }

        $annots = $this->delegate->getClassAnnotations($class);
        $this->cache->putClassAnnotationsInCache($class, $annots);

        return $annots;
    }

    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        foreach ($this->getClassAnnotations($class) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        if (null !== $annots = $this->cache->getPropertyAnnotationsFromCache($property)) {
            return $annots;
        }

        $annots = $this->delegate->getPropertyAnnotations($property);
        $this->cache->putPropertyAnnotationsInCache($property, $annots);

        return $annots;
    }

    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        foreach ($this->getPropertyAnnotations($property) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        if (null !== $annots = $this->cache->getMethodAnnotationsFromCache($method)) {
            return $annots;
        }

        $annots = $this->delegate->getMethodAnnotations($method);
        $this->cache->putMethodAnnotationsInCache($method, $annots);

        return $annots;
    }

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }
}