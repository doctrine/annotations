<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetPropertyMethod;

class ClassWithAtInDescriptionAndAnnotation
{
    /**
     * Lala
     *
     * {
     *     "email": "foo@example.com"
     * }
     *
     * @AnnotationTargetPropertyMethod("Bar")
     */
    public $foo;
}
