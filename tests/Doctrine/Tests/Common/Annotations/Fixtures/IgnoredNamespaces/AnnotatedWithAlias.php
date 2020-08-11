<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

use SomePropertyAnnotationNamespace\Subnamespace as SomeAlias;

class AnnotatedWithAlias
{
    /** @SomeAlias\Name */
    private $property;
}
