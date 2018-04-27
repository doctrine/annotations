<?php

namespace Doctrine\Tests\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtPropertyLevel
{
    /**
     * @SomePropertyAnnotationNamespace\Subnamespace\Name
     */
    private $property;
}
