<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/** @Annotation */
class DummyAnnotation extends \Doctrine\Annotations\Annotation {
    public $dummyValue;
}