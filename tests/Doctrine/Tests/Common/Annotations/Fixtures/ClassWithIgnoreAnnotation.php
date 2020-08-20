<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @ignoreAnnotation("IgnoreAnnotationClass")
 */
class ClassWithIgnoreAnnotation
{
    /**
     * @var mixed[]
     * @IgnoreAnnotationClass
     */
    public $foo;
}
