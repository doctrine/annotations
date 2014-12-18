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

namespace Doctrine\Common\Annotations;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\Cache;

/**
 * File cache reader for annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class FileCacheReader implements Reader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var array
     */
    private $loadedAnnotations = array();

    /**
     * @var array
     */
    private $classNameHashes = array();

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param Reader  $reader
     * @param string  $cacheDir
     * @param boolean $debug
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Reader $reader, $cacheDir, $debug = false)
    {
        $this->reader = $reader;
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $cacheDir));
        }

        $this->dir   = rtrim($cacheDir, '\\/');
        $this->cache = new FilesystemCache($this->dir, '.annotationscache.data');

        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(\ReflectionClass $class)
    {
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name];

        if (isset($this->loadedAnnotations[$key])) {
            return $this->loadedAnnotations[$key];
        }

        $cacheKey = $key . filemtime($class->getFileName());
        $cached = $this->cache->fetch($cacheKey);
        if (false === $cached) {
            $annot = $this->reader->getClassAnnotations($class);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        if ($this->debug && (false !== $filename = $class->getFilename())) {
            $this->cache->delete($cacheKey);

            $annot = $this->reader->getClassAnnotations($class);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        return $this->loadedAnnotations[$key] = $cached;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name].'$'.$property->getName();

        if (isset($this->loadedAnnotations[$key])) {
            return $this->loadedAnnotations[$key];
        }

        $cacheKey = $key . filemtime($class->getFileName());
        $cached = $this->cache->fetch($cacheKey);
        if (false === $cached) {
            $annot = $this->reader->getPropertyAnnotations($property);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        if ($this->debug && (false !== $filename = $class->getFilename())) {
            $this->cache->delete($cacheKey);

            $annot = $this->reader->getPropertyAnnotations($property);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        return $this->loadedAnnotations[$key] = $cached;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name].'#'.$method->getName();

        if (isset($this->loadedAnnotations[$key])) {
            return $this->loadedAnnotations[$key];
        }

        $cacheKey = $key . filemtime($class->getFileName());
        $cached = $this->cache->fetch($cacheKey);
        if (false === $cached) {
            $annot = $this->reader->getMethodAnnotations($method);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        if ($this->debug && (false !== $filename = $class->getFilename())) {
            $this->cache->delete($cacheKey);

            $annot = $this->reader->getMethodAnnotations($method);
            $this->saveCacheFile($cacheKey, $annot);
            return $this->loadedAnnotations[$key] = $annot;
        }

        return $this->loadedAnnotations[$key] = $cached;
    }

    /**
     * Saves the cache file.
     *
     * @param string $path
     * @param mixed  $data
     *
     * @return void
     */
    private function saveCacheFile($path, $data)
    {
        $this->cache->save($path, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        $annotations = $this->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Clears loaded annotations.
     *
     * @return void
     */
    public function clearLoadedAnnotations()
    {
        $this->loadedAnnotations = array();
    }
}
