<?php

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 */
class AnnotationTargetAll
{
    public $data;
    public $name;
    public $target;
}
