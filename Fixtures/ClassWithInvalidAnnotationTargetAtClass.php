<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetMethod;

/**
 * @AnnotationTargetMethod("Some data")
 */
class ClassWithInvalidAnnotationTargetAtClass
{
    
    /**
     * @AnnotationTargetMethod("Bar")
     */
    public $foo;
}