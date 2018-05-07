<?php

namespace Doctrine\Tests\Annotations\Fixtures\Annotation;

/** @Annotation */
class AnnotWithDefaultValue
{
    /** @var string */
    public $foo = 'bar';
}
