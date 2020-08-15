<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtPropertyLevel
{
    /**
     * @var mixed
     * @SomePropertyAnnotationNamespace\Subnamespace\Name
     */
    public $property;
}
