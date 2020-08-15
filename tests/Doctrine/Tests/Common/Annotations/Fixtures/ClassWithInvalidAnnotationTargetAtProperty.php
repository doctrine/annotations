<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithInvalidAnnotationTargetAtProperty
{
    /**
     * @var mixed
     * @AnnotationTargetClass("Bar")
     */
    public $foo;


    /**
     * @var mixed
     * @AnnotationTargetAnnotation("Foo")
     */
    public $bar;
}
