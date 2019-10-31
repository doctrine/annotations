<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/** @Annotation */
class AnnotWithContext
{
    public function __construct(array $data, $context)
    {
        if ($data['value'] === 'invalid') {
            $class = explode('\\', __CLASS__);
            $class = end($class);
            throw new \InvalidArgumentException(sprintf('Invalid default value for "@%s" annotation in %s', $class, $context));
        }
    }
}
