<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces;
use Doctrine\Tests\Common\Annotations\Fixtures\Traits\AnnotateAtMethodLevelTrait;

/**
 * @SomeClassAnnotationNamespace\Subnamespace\Name
 */
class AnnotatedAtClassLevel
{
    use AnnotateAtMethodLevelTrait;
}
