<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAnnotation;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithInvalidAnnotationTargetAtProperty
{

    /**
     * @AnnotationTargetClass("Bar")
     */
    public $foo;


    /**
     * @AnnotationTargetAnnotation("Foo")
     */
    public $bar;
}