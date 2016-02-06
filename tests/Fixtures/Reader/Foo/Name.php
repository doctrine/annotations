<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader\Foo;

/** @Annotation */
class Name extends \Doctrine\Annotations\Annotation
{
    public $name;
}