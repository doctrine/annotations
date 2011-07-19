<?php

namespace Doctrine\Common\Annotations;

/**
 * Simple Annotation Reader.
 *
 * This annotation reader is intended to be used in projects where you have
 * full-control over all annotations that are available.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SimpleAnnotationReader implements Reader
{
    private $parser;

    public function __construct()
    {
        $this->parser = new DocParser();
        $this->parser->setIgnoreNotImportedAnnotations(true);
    }

    /**
     * Adds a namespace in which we will look for annotations.
     *
     * @param string $namespace
     */
    public function addNamespace($namespace)
    {
        $this->parser->addNamespace($namespace);
    }

    public function getClassAnnotations(\ReflectionClass $class)
    {
        return $this->parser->parse($class->getDocComment(), 'class '.$class->getName());
    }

    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        return $this->parser->parse($method->getDocComment(), 'method '.$class->getName().'::'.$method->getName().'()');
    }

    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        return $this->parser->parse($property->getDocComment(), 'property '.$class->getName().'::$'.$property->getName());
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

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }

        return null;
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
}