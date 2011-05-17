<?php

namespace Doctrine\Common\Annotations\Cache;

use Doctrine\Common\Cache\Cache;

class DoctrineCacheAdapter implements CacheInterface
{
    const PREFIX = '[@Annotations]';

    private $cache;
    private $prefix;
    private $annotations = array();

    public function __construct(Cache $cache, $prefix = self::PREFIX)
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    public function getClassAnnotationsFromCache(\ReflectionClass $class)
    {
        $key = $this->prefix.$class->getName();

        if (isset($this->annotations[$key])) {
            return $this->annotations[$key];
        }

        if (!$this->cache->has($key)) {
            return null;
        }

        return $this->annotations[$key] = unserialize($this->cache->fetch($key));
    }

    public function getMethodAnnotationsFromCache(\ReflectionMethod $method)
    {
        $key = $this->prefix.$method->getDeclaringClass()->getName().'#'.$method->getName();

        if (isset($this->annotations[$key])) {
            return $this->annotations[$key];
        }

        if (!$this->cache->has($key)) {
            return null;
        }

        return $this->annotations[$key] = unserialize($this->cache->fetch($key));
    }

    public function getPropertyAnnotationsFromCache(\ReflectionProperty $property)
    {
        $key = $this->prefix.$property->getDeclaringClass()->getName().'$'.$property->getName();

        if (isset($this->annotations[$key])) {
            return $this->annotations[$key];
        }

        if (!$this->cache->has($key)) {
            return null;
        }

        return $this->annotations[$key] = unserialize($this->cache->fetch($key));
    }

    public function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations)
    {
        $key = $this->prefix.$class->getName();
        $this->annotations[$key] = $annotations;
        $this->cache->save($key, serialize($annotations));
    }

    public function putPropertyAnnotationsInCache(\ReflectionProperty $property, array $annotations)
    {
        $key = $this->prefix.$property->getDeclaringClass()->getName().'$'.$property->getName();
        $this->annotations[$key] = $annotations;
        $this->cache->save($key, serialize($annotations));
    }

    public function putMethodAnnotationsInCache(\ReflectionMethod $method, array $annotations)
    {
        $key = $this->prefix.$method->getDeclaringClass()->getName().'#'.$method->getName();
        $this->annotations[$key] = $annotations;
        $this->cache->save($key, serialize($annotations));
    }
}