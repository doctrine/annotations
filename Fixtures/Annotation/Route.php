<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

class Route extends \Doctrine\Common\Annotations\Annotation
{
    private $pattern;
    private $name;
}