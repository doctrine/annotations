<?php

namespace Doctrine\Tests\Annotations\Fixtures\IgnoredNamespaces;

class AnnotatedAtMethodLevel
{
    /**
     * @SomeMethodAnnotationNamespace\Subnamespace\Name
     */
    public function test() : void {}
}
