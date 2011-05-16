<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

class Template
{
    private $name;

    public function __construct(array $values)
    {
        $this->name = isset($values['value']) ? $values['value'] : null;
    }
}