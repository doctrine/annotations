<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationTargetAll
{
    public $data;
    public $name;
    public $target;
}