<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * Class ClassWithNotRegisteredAnnotationUsed
 * @package Doctrine\Tests\Common\Annotations\Fixtures
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