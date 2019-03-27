<?php

namespace Doctrine\Tests\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtConstantLevel
{
    /**
     * @SomeConstantAnnotationNamespace\Subnamespace\Name
     */
    const SOME_CONSTANT = "foo";
}
