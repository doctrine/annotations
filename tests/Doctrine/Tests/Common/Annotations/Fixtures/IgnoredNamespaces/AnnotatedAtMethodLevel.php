<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtMethodLevel
{
    /**
     * @SomeMethodAnnotationNamespace\Subnamespace\Name
     */
    public function test() {}
}
