<?php

namespace Doctrine\Tests\Annotations\Fixtures;


/**
 * @Annotation
 * @Target("METHOD")
 */
final class AnnotationTargetMethod
{
    public $data;
    public $name;
    public $target;
}
