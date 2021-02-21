<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtConstantLevel
{
    /**
     * @SomeConstantAnnotationNamespace\Subnamespace\Name
     */
    const SOME_CONSTANT = "foo";
}
