<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/** @Annotation */
class DummyColumn extends \Doctrine\Annotations\Annotation {
    public $type;
}