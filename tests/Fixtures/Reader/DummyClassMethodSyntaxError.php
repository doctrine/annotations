<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

class DummyClassMethodSyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public function foo()
    {

    }
}
