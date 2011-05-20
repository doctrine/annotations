<?php

namespace Doctrine\Common\Annotations\Cache;

use Doctrine\Common\Cache\Cache as DoctrineCache;

class DoctrineCacheAdapter implements Cache
{
    const PREFIX = '[@Annot]';

    private $cache;
    private $prefix;
    private $debug;

    public function __construct(DoctrineCache $cache, $debug = false, $prefix = self::PREFIX)
    {
        $this->cache = $cache;
        $this->debug = $debug;
        $this->prefix = $prefix;
    }

    public function getClassAnnotationsFromCache(\ReflectionClass $class)
    {
        $key = $class->getName();

        if (!$this->cache->has($this->prefix.$key)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && $this->cache->fetch($this->prefix.'[C]'.$key) < filemtime($filename)) {
            $this->cache->delete($this->prefix.$key);
            $this->cache->delete($this->prefix.'[C]'.$key);

            return null;
        }

        return unserialize($this->cache->fetch($this->prefix.$key));
    }

    public function getMethodAnnotationsFromCache(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $key = $class->getName().'#'.$method->getName();

        if (!$this->cache->has($this->prefix.$key)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && $this->cache->fetch($this->prefix.'[C]'.$key) < filemtime($filename)) {
            $this->cache->delete($this->prefix.$key);
            $this->cache->delete($this->prefix.'[C]'.$key);

            return null;
        }

        return unserialize($this->cache->fetch($this->prefix.$key));
    }

    public function getPropertyAnnotationsFromCache(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $key = $class->getName().'$'.$property->getName();

        if (!$this->cache->has($this->prefix.$key)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && $this->cache->fetch($this->prefix.'[C]'.$key) < filemtime($filename)) {
            $this->cache->delete($this->prefix.$key);
            $this->cache->delete($this->prefix.'[C]'.$key);

            return null;
        }

        return unserialize($this->cache->fetch($this->prefix.$key));
    }

    public function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations)
    {
        $key = $class->getName();
        $this->cache->save($this->prefix.$key, serialize($annotations));
        $this->cache->save($this->prefix.'[C]'.$key, time());
    }

    public function putPropertyAnnotationsInCache(\ReflectionProperty $property, array $annotations)
    {
        $key = $property->getDeclaringClass()->getName().'$'.$property->getName();
        $this->cache->save($this->prefix.$key, serialize($annotations));
        $this->cache->save($this->prefix.'[C]'.$key, time());
    }

    public function putMethodAnnotationsInCache(\ReflectionMethod $method, array $annotations)
    {
        $key = $method->getDeclaringClass()->getName().'#'.$method->getName();
        $this->cache->save($this->prefix.$key, serialize($annotations));
        $this->cache->save($this->prefix.'[C]'.$key, time());
    }
}