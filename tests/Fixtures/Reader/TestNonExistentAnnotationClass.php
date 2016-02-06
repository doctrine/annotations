<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

class TestNonExistentAnnotationClass
{
    /**
     * @Foo\Bar\Name
     */
    private $field;
}