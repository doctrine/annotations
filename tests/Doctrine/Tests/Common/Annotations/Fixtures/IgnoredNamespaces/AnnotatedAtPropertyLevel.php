<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtPropertyLevel
{
    /**
     * @SomePropertyAnnotationNamespace\Subnamespace\Name
     */
    private $property;
}
