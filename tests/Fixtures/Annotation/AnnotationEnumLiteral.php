<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnumLiteral as SelfEnum;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationEnumLiteral
{
    const ONE   = 1;
    const TWO   = 2;
    const THREE = 3;

    /**
     * @var mixed
     *
     * @Enum(
     *      value = {
     *          1,
     *          2,
     *          3,
     *      },
     *      literal = {
     *          1 : "AnnotationEnumLiteral::ONE",
     *          2 : "AnnotationEnumLiteral::TWO",
     *          3 : "AnnotationEnumLiteral::THREE",
     *      }
     * )
     */
    public $value;
}