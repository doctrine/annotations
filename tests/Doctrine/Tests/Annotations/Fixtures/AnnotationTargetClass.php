<?php

namespace Doctrine\Tests\Annotations\Fixtures;


/**
 * @Annotation
 * @Target("CLASS")
 */
final class AnnotationTargetClass
{
    public $data;
    public $name;
    public $target;
}
