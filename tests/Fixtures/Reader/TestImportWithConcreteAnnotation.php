<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

class TestImportWithConcreteAnnotation
{
    /**
     * @DummyAnnotation(dummyValue = "bar")
     */
    private $field;
}