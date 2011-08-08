<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetNestedAnnotation;

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
     * @AnnotationTargetNestedAnnotation("Foo")
     */
    public $bar;
}