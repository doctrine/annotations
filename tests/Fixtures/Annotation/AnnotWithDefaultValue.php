<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/** @Annotation */
class AnnotWithDefaultValue
{
    /** @var string */
    public $foo = 'bar';
}