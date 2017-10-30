<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
class Name extends Annotation {
    public $foo;
}
