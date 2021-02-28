<?php

namespace Doctrine\Common\Annotations;

use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

use function array_map;
use function array_merge;
use function assert;
use function filemtime;
use function max;
use function rawurlencode;
use function time;

/**
 * A cache aware annotation reader.
 */
final class PsrCachedReader implements Reader
{
    /** @var Reader */
    private $delegate;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var bool */
    private $debug;

    /** @var array<string, array<object>> */
    private $loadedAnnotations = [];

    /** @var int[] */
    private $loadedFilemtimes = [];

    public function __construct(Reader $reader, CacheItemPoolInterface $cache, bool $debug = false)
    {
        $this->delegate = $reader;
        $this->cache    = $cache;
        $this->debug    = (bool) $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $cacheKey = $class->getName();

        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }

        $annots = $this->fetchFromCache($cacheKey, $class, 'getClassAnnotations', $class);

        return $this->loadedAnnotations[$cacheKey] = $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        foreach ($this->getClassAnnotations($class) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $class    = $property->getDeclaringClass();
        $cacheKey = $class->getName() . '$' . $property->getName();

        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }

        $annots = $this->fetchFromCache($cacheKey, $class, 'getPropertyAnnotations', $property);

        return $this->loadedAnnotations[$cacheKey] = $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        foreach ($this->getPropertyAnnotations($property) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $class    = $method->getDeclaringClass();
        $cacheKey = $class->getName() . '#' . $method->getName();

        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }

        $annots = $this->fetchFromCache($cacheKey, $class, 'getMethodAnnotations', $method);

        return $this->loadedAnnotations[$cacheKey] = $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    public function clearLoadedAnnotations(): void
    {
        $this->loadedAnnotations = [];
        $this->loadedFilemtimes  = [];
    }

    /** @return mixed[] */
    private function fetchFromCache(
        string $cacheKey,
        ReflectionClass $class,
        string $method,
        Reflector $reflector
    ): array {
        $cacheKey = rawurlencode($cacheKey);

        $item = $this->cache->getItem($cacheKey);
        if (! $item->isHit() || ($this->debug && ! $this->refresh($cacheKey, $class))) {
            $this->cache->save($item->set($this->delegate->{$method}($reflector)));
        }

        return $item->get();
    }

    /**
     * Used in debug mode to check if the cache is fresh.
     *
     * @return bool Returns true if the cache was fresh, or false if the class
     * being read was modified since writing to the cache.
     */
    private function refresh(string $cacheKey, ReflectionClass $class): bool
    {
        $lastModification = $this->getLastModification($class);
        if ($lastModification === 0) {
            return true;
        }

        $item = $this->cache->getItem('[C]' . $cacheKey);
        if ($item->isHit() && $item->get() >= $lastModification) {
            return true;
        }

        $this->cache->save($item->set(time()));

        return false;
    }

    /**
     * Returns the time the class was last modified, testing traits and parents
     */
    private function getLastModification(ReflectionClass $class): int
    {
        $filename = $class->getFileName();

        if (isset($this->loadedFilemtimes[$filename])) {
            return $this->loadedFilemtimes[$filename];
        }

        $parent = $class->getParentClass();

        $lastModification =  max(array_merge(
            [$filename ? filemtime($filename) : 0],
            array_map(function (ReflectionClass $reflectionTrait): int {
                return $this->getTraitLastModificationTime($reflectionTrait);
            }, $class->getTraits()),
            array_map(function (ReflectionClass $class): int {
                return $this->getLastModification($class);
            }, $class->getInterfaces()),
            $parent ? [$this->getLastModification($parent)] : []
        ));

        assert($lastModification !== false);

        return $this->loadedFilemtimes[$filename] = $lastModification;
    }

    private function getTraitLastModificationTime(ReflectionClass $reflectionTrait): int
    {
        $fileName = $reflectionTrait->getFileName();

        if (isset($this->loadedFilemtimes[$fileName])) {
            return $this->loadedFilemtimes[$fileName];
        }

        $lastModificationTime = max(array_merge(
            [$fileName ? filemtime($fileName) : 0],
            array_map(function (ReflectionClass $reflectionTrait): int {
                return $this->getTraitLastModificationTime($reflectionTrait);
            }, $reflectionTrait->getTraits())
        ));

        assert($lastModificationTime !== false);

        return $this->loadedFilemtimes[$fileName] = $lastModificationTime;
    }
}
