<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/**
 * @Annotation
 * @Target({ "ANNOTATION" })
 */
final class AnnotationTargetAnnotation
{
    public $data;
    public $name;
    public $target;
}