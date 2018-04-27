<?php

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * Class ClassWithNotRegisteredAnnotationUsed
 * @package Doctrine\Tests\Annotations\Fixtures
 */
class ClassWithNotRegisteredAnnotationUsed
{
    /**
     * @notRegisteredCustomAnnotation
     * @return bool
     */
    public function methodWithNotRegisteredAnnotation()
    {
        return false;
    }
}
