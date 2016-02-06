<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;


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