<?php

namespace Doctrine\AnnotationsTests\Fixtures;

/**
 * @ignoreAnnotation({"IgnoreAnnotationClass"})
 */
class ClassWithIgnoreAnnotation
{
    /**
     * @IgnoreAnnotationClass
     */
    public $foo;
}
