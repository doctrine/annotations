<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/** @Annotation */
class DummyJoinTable extends \Doctrine\Annotations\Annotation {
    public $name;
    public $joinColumns;
    public $inverseJoinColumns;
}