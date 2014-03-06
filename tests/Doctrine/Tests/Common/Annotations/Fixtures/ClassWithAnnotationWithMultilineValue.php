<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetPropertyMethod;

class ClassWithAnnotationWithMultilineValue
{
    /**
     * @AnnotationTargetPropertyMethod("Foo
     * Bar
     * Baz")
     */
    public $foo;
}
