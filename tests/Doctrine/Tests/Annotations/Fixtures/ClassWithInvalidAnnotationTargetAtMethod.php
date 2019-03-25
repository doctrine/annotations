<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetClass;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithInvalidAnnotationTargetAtMethod
{

    /**
     * @AnnotationTargetClass("functionName")
     */
    public function functionName($param) : void
    {

    }
}
