<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/** @Annotation */
class DummyJoinColumn extends \Doctrine\Annotations\Annotation {
    public $name;
    public $referencedColumnName;
}