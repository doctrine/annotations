<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationWithRequiredAttributesWithoutContructor
{

    /**
     * @Required
     * @var string
     */
    public $value;

    /**
     * @Required
     * @var Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAnnotation
     */
    public $annot;

}