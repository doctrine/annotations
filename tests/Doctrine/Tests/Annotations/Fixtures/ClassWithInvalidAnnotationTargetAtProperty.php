<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetClass;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAnnotation;

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
