<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationEnum
{
    const ONE   = 'ONE';
    const TWO   = 'TWO';
    const THREE = 'THREE';

    /**
     * @var mixed
     *
     * @Enum({"ONE","TWO","THREE"})
     */
    public $value;
}