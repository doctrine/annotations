<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @Annotation
 * @Target({ "NESTED_ANNOTATION" })
 */
final class AnnotationTargetNestedAnnotation
{
    public $data;
    public $name;
    public $target;
}