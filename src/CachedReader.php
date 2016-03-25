<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

declare(strict_types=1);

namespace Doctrine\Annotations;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use Doctrine\Common\Cache\Cache;

/**
 * A cache aware annotation reader.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@hotmail.com>
 */
final class CachedReader implements Reader
{
    /**
     * @var string
     */
    const CACHE_SALT = '@[Annot]';

    /**
     * @var \Doctrine\Annotations\Reader
     */
    private $delegate;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param \Doctrine\Annotations\Reader $reader
     * @param \Doctrine\Common\Cache\Cache $cache
     * @param bool                         $debug
     */
    public function __construct(Reader $reader, Cache $cache, bool $debug = false)
    {
        $this->delegate = $reader;
        $this->cache    = $cache;
        $this->debug    = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class) : array
    {
        $cacheKey  = $class->getName();
        $cacheData = $this->fetchFromCache($cacheKey, $class);

        if ($cacheData !== false) {
            return $cacheData;
        }

        $annots = $this->delegate->getClassAnnotations($class);

        $this->saveToCache($cacheKey, $annots);

        return $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(ReflectionClass $class, string $annotationName)
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
    public function getPropertyAnnotations(ReflectionProperty $property) : array
    {
        $class     = $property->getDeclaringClass();
        $cacheKey  = $class->getName() . '$' . $property->getName();
        $cacheData = $this->fetchFromCache($cacheKey, $class);

        if ($cacheData !== false) {
            return $cacheData;
        }

        $annots = $this->delegate->getPropertyAnnotations($property);

        $this->saveToCache($cacheKey, $annots);

        return $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, string $annotationName)
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
    public function getMethodAnnotations(ReflectionMethod $method) : array
    {
        $class     = $method->getDeclaringClass();
        $cacheKey  = $class->getName() . '#' . $method->getName();
        $cacheData = $this->fetchFromCache($cacheKey, $class);

        if ($cacheData !== false) {
            return $cacheData;
        }

        $annots = $this->delegate->getMethodAnnotations($method);

        $this->saveToCache($cacheKey, $annots);

        return $annots;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, string $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string           $rawCacheKey The cache key.
     * @param \ReflectionClass $class       The related class.
     *
     * @return mixed The cached value or false when the value is not in cache.
     */
    private function fetchFromCache(string $rawCacheKey, \ReflectionClass $class)
    {
        $key  = $rawCacheKey . self::CACHE_SALT;
        $data = $this->cache->fetch($key);

        if ( ! isset($data['value'], $data['time'])) {
            return false;
        }

        if ( ! $this->debug) {
            return $data['value'];
        }

        if ( ! $this->isCacheFresh($data['time'], $class)) {
            return false;
        }

        return $data['value'];
    }

    /**
     * Saves a value to the cache.
     *
     * @param string $rawCacheKey The cache key.
     * @param mixed  $value       The value.
     *
     * @return void
     */
    private function saveToCache(string $rawCacheKey, $value)
    {
        $key  = $rawCacheKey . self::CACHE_SALT;
        $data = [
            'value' => $value,
            'time'  => time()
        ];

        $this->cache->save($key, $data);
    }

    /**
     * Checks if the cache is fresh.
     *
     * @param int              $time
     * @param \ReflectionClass $class
     *
     * @return bool
     */
    private function isCacheFresh(int $time, ReflectionClass $class) : bool
    {
        $filename  = $class->getFilename();
        $filemtime = (false !== $filename)
            ? filemtime($filename)
            : -1;

        return $time >= $filemtime;
    }
}
