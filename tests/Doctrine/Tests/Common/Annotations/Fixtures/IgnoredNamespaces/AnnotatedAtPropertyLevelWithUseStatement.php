<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

use SomePropertyAnnotationNamespace\Subnamespace;

class AnnotatedAtPropertyLevelWithUseStatement
{
    /**
     * @Subnamespace\Name
     */
    private $property;
}
