<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

class DummyClassPropertySyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public $foo;
}