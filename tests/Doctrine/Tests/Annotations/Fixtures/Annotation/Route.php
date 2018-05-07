<?php

namespace Doctrine\Tests\Annotations\Fixtures\Annotation;

/** @Annotation */
class Route
{
    /** @var string @Required */
    public $pattern;
    public $name;
}
