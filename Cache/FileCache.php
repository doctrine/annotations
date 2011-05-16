<?php

namespace Doctrine\Common\Annotations\Cache;

class FileCache implements CacheInterface
{
    private $dir;
    private $debug;
    private $classAnnotations = array();
    private $propertyAnnotations = array();
    private $methodAnnotations = array();

    public function __construct($dir, $debug = false)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }
        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $this->dir   = rtrim($dir, '\\/');
        $this->debug = $debug;
    }

    public function getClassAnnotationsFromCache(\ReflectionClass $class)
    {
        $key = $class->getName();

        if (isset($this->classAnnotations[$key])) {
            return $this->classAnnotations[$key];
        }

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);

            return null;
        }

        return $this->classAnnotations[$key] = include $path;
    }

    public function getPropertyAnnotationsFromCache(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $key = $class->getName().'$'.$property->getName();

        if (isset($this->propertyAnnotations[$key])) {
            return $this->propertyAnnotations[$key];
        }

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);

            return null;
        }

        return $this->propertyAnnotations[$key] = include $path;
    }

    public function getMethodAnnotationsFromCache(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $key = $class->getName().'#'.$method->getName();

        if (isset($this->methodAnnotations[$key])) {
            return $this->methodAnnotations[$key];
        }

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);

            return null;
        }

        return $this->methodAnnotations[$key] = include $path;
    }

    public function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations)
    {
        $key = $class->getName();

        $this->classAnnotations[$key] = $annotations;
        $this->saveCacheFile($this->dir.'/'.strtr($key, '\\', '-').'.cache.php', $annotations);
    }

    public function putPropertyAnnotationsInCache(\ReflectionProperty $property, array $annotations)
    {
        $key = $property->getDeclaringClass()->getName().'$'.$property->getName();

        $this->propertyAnnotations[$key] = $annotations;
        $this->saveCacheFile($this->dir.'/'.strtr($key, '\\', '-').'.cache.php', $annotations);
    }

    public function putMethodAnnotationsInCache(\ReflectionMethod $method, array $annotations)
    {
        $key = $method->getDeclaringClass()->getName().'#'.$method->getName();

        $this->methodAnnotations[$key] = $annotations;
        $this->saveCacheFile($this->dir.'/'.strtr($key, '\\', '-').'.cache.php', $annotations);
    }

    private function saveCacheFile($path, array $annotations)
    {
        file_put_contents($path, '<?php return unserialize('.var_export(serialize($annotations), true).');');
    }
}