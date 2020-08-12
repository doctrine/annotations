<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @package Doctrine\Tests\Common\Annotations\Fixtures
 */
class ClassWithNotRegisteredAnnotationUsed
{
    /**
     * @return bool
     *
     * @notRegisteredCustomAnnotation
     */
    public function methodWithNotRegisteredAnnotation()
    {
        return false;
    }
}
