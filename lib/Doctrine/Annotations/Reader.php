<?php

namespace Doctrine\Annotations;

/**
 * Interface for annotation readers.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Reader
{
    /**
     * Gets the annotations applied to a class.
     *
     * @param \ReflectionClass $class The ReflectionClass of the class from which
     *                                the class annotations should be read.
     *
     * @return array An array of Annotations.
     */
    function getClassAnnotations(\ReflectionClass $class);

    /**
     * Gets a class annotation.
     *
     * @param \ReflectionClass $class          The ReflectionClass of the class from which
     *                                         the class annotations should be read.
     * @param string           $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    function getClassAnnotation(\ReflectionClass $class, $annotationName);

    /**
     * Gets the annotations applied to a method.
     *
     * @param \ReflectionMethod $method The ReflectionMethod of the method from which
     *                                  the annotations should be read.
     *
     * @return array An array of Annotations.
     */
    function getMethodAnnotations(\ReflectionMethod $method);

    /**
     * Gets a method annotation.
     *
     * @param \ReflectionMethod $method         The ReflectionMethod to read the annotations from.
     * @param string            $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    function getMethodAnnotation(\ReflectionMethod $method, $annotationName);

    /**
     * Gets the annotations applied to a property.
     *
     * @param \ReflectionProperty $property The ReflectionProperty of the property
     *                                      from which the annotations should be read.
     *
     * @return array An array of Annotations.
     */
    function getPropertyAnnotations(\ReflectionProperty $property);

    /**
     * Gets a property annotation.
     *
     * @param \ReflectionProperty $property       The ReflectionProperty to read the annotations from.
     * @param string              $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    function getPropertyAnnotation(\ReflectionProperty $property, $annotationName);

    /**
     * Gets the annotations applied to a constant.
     *
     * @param \ReflectionClassConstant $constant The ReflectionClassConstant of the constant
     *                                           from which the annotations should be read.
     *
     * @return array An array of Annotations.
     */
    function getConstantAnnotations(\ReflectionClassConstant $constant);

    /**
     * Gets a constant annotation.
     *
     * @param \ReflectionClassConstant $constant       The ReflectionClassConstant to read the annotations from.
     * @param string                   $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    function getConstantAnnotation(\ReflectionClassConstant $constant, $annotationName);
}
