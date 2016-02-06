<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod;

/**
 * @AnnotationTargetPropertyMethod("Some data")
 */
class ClassWithInvalidAnnotationTargetAtClass
{

    /**
     * @AnnotationTargetPropertyMethod("Bar")
     */
    public $foo;
}