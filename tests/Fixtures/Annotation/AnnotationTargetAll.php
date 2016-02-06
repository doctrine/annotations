<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

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