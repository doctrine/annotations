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


/**
 * File cache reader for annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Gabor Toth <tgabi333@gmail.com>
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

    private $classNameHashes = array();

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param string $cacheDir
     * @param bool $debug
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Reader $reader, $cacheDir, $debug = false)
    {
        $this->reader = $reader;
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $cacheDir));
        }
        if (!is_writable($cacheDir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable. Both, the webserver and the console user need access. You can manage access rights for multiple users with "chmod +a". If your system does not support this, check out the acl package.', $cacheDir));
        }

        $this->dir   = rtrim($cacheDir, '\\/');
        $this->debug = $debug;
    }

    /**
     * Retrieve annotations for class
     *
     * @param \ReflectionClass $class
     * @return array
     */
    public function getClassAnnotations(\ReflectionClass $class)
    {
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name];

        if (isset($this->loadedAnnotations[$key]['class'])) {
            return $this->loadedAnnotations[$key]['class'];
        }

        if ($this->isCached($class)) {
            $this->loadedAnnotations[$key] = $this->loadCache($class);
        } else {
            $this->loadedAnnotations[$key] = $this->buildCache($class);
        }

        if ($this->debug) {
            $this->refreshCache($class);
        }

        if (isset($this->loadedAnnotations[$key]['class'])) {
            return $this->loadedAnnotations[$key]['class'];
        }

        return $this->reader->getClassAnnotations($class);
    }

    /**
     * Get annotations for property
     *
     * @param \ReflectionProperty $property
     * @return array
     */
    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name];

        if (isset($this->loadedAnnotations[$key]['property'][$property->getName()])) {
            return $this->loadedAnnotations[$key]['property'][$property->getName()];
        }

        if ($this->isCached($class)) {
            $this->loadedAnnotations[$key] = $this->loadCache($class);
        } else {
            $this->loadedAnnotations[$key] = $this->buildCache($class);
        }

        if ($this->debug) {
            $this->refreshCache($class);
        }

        if (isset($this->loadedAnnotations[$key]['property'][$property->getName()])) {
            return $this->loadedAnnotations[$key]['property'][$property->getName()];
        }

        return $this->reader->getPropertyAnnotations($property);
    }

    /**
     * Retrieve annotations for method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        if ( ! isset($this->classNameHashes[$class->name])) {
            $this->classNameHashes[$class->name] = sha1($class->name);
        }
        $key = $this->classNameHashes[$class->name];

        if (isset($this->loadedAnnotations[$key]['method'][$method->getName()])) {
            return $this->loadedAnnotations[$key]['method'][$method->getName()];
        }

        if ($this->isCached($class)) {
            $this->loadedAnnotations[$key] = $this->loadCache($class);
        } else {
            $this->loadedAnnotations[$key] = $this->buildCache($class);
        }

        if ($this->debug) {
            $this->refreshCache($class);
        }

        if (isset($this->loadedAnnotations[$key]['method'][$method->getName()])) {
            return $this->loadedAnnotations[$key]['method'][$method->getName()];
        }

        return $this->reader->getMethodAnnotations($method);
    }

    /**
     * Gets a class annotation.
     *
     * @param \ReflectionClass $class The ReflectionClass of the class from which
     *                               the class annotations should be read.
     * @param string $annotationName The name of the annotation.
     *
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
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
     * Gets a method annotation.
     *
     * @param \ReflectionMethod $method
     * @param string $annotationName The name of the annotation.
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
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
     * Gets a property annotation.
     *
     * @param \ReflectionProperty $property
     * @param string $annotationName The name of the annotation.
     * @return mixed The Annotation or NULL, if the requested annotation does not exist.
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
     * Clear stores annotations
     */
    public function clearLoadedAnnotations()
    {
        $this->loadedAnnotations = array();
    }

    /**
     * Builds cache file for a class
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function buildCache(\ReflectionClass $class)
    {
        $path = $this->getCachePath($class);

        $data = array();
        try {
            $data['class'] = $this->reader->getClassAnnotations($class);
        } catch (AnnotationException $e) {
        }

        foreach ($class->getMethods() as $method) {
            try {
                $data['method'][$method->getName()] = $this->reader->getMethodAnnotations($method);
            } catch (AnnotationException $e) {
            }
        }

        foreach ($class->getProperties() as $property) {
            try {
                $data['property'][$property->getName()] = $this->reader->getPropertyAnnotations($property);
            } catch (AnnotationException $e) {
            }
        }

        file_put_contents($path, '<?php return unserialize('.var_export(serialize($data), true).');');

        return $data;
    }

    /**
     * Loads cached info for a class
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function loadCache(\ReflectionClass $class)
    {
        return include $this->getCachePath($class);
    }

    /**
     * Returns whether a cache file exists.
     *
     * @param \ReflectionClass $class
     *
     * @return bool
     */
    private function isCached(\ReflectionClass $class)
    {
        return file_exists($this->getCachePath($class));
    }

    /**
     * Refresh cache file if needed.
     *
     * @param \ReflectionClass $class
     */
    private function refreshCache(\ReflectionClass $class)
    {
        $filename = $class->getFilename();
        if (!empty($filename)) {
            $path = $this->getCachePath($class);

            if (filemtime($path) < filemtime($filename)) {
                @unlink($path);
                $key = $class->getName();
                $this->loadedAnnotations[$key] = $this->buildCache($class);
            }
        }
    }

    /**
     * Gets the path of cache file for a class
     *
     * @param \ReflectionClass $class
     */
    private function getCachePath(\ReflectionClass $class)
    {
        return $this->dir.'/'.strtr($class->getName(), '\\', '_').'.cache.php';
    }
}
