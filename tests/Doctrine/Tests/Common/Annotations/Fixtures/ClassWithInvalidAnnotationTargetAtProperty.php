<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithInvalidAnnotationTargetAtProperty
{
    /** @AnnotationTargetClass("Bar") */
    public $foo;


    /** @AnnotationTargetAnnotation("Foo") */
    public $bar;
}
