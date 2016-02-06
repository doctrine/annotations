<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/** @Annotation */
class Template
{
    private $name;

    public function __construct(array $values)
    {
        $this->name = isset($values['value']) ? $values['value'] : null;
    }

    public function getName()
    {
        return $this->name;
    }
}