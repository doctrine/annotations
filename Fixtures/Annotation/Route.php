<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

class Route
{
    private $pattern;
    private $name;

    public function __construct(array $values)
    {
        $this->pattern = $values['value'];
        $this->name    = isset($values['name'])? $values['name'] : null;
    }
}