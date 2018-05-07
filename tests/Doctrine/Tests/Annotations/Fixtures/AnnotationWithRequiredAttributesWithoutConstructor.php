<?php

namespace Doctrine\Tests\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationWithRequiredAttributesWithoutConstructor
{

    /**
     * @Required
     * @var string
     */
    public $value;

    /**
     * @Required
     * @var \Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAnnotation
     */
    public $annot;

}
