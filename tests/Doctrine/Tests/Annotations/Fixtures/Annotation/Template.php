<?php

namespace Doctrine\Tests\Annotations\Fixtures\Annotation;

/** @Annotation */
class Template
{
    private $name;

    public function __construct(array $values)
    {
        $this->name = $values['value'] ?? null;
    }
}
