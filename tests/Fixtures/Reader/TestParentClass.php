<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

class TestParentClass extends TestChildClass
{
    /**
     * @\Doctrine\AnnotationsTests\Fixtures\Reader\Bar\Name(name = "bar")
     */
    private $parent;
}