<?php

namespace Doctrine\AnnotationsTests\Fixtures\Parser;

/** @Annotation */
class SomeAnnotationWithConstructorWithoutParams
{
    function __construct()
    {
        $this->data = "Some data";
    }
    public $data;
    public $name;
}