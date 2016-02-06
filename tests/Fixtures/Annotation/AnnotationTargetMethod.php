<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;


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