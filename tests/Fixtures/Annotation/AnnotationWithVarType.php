<?php

namespace Doctrine\AnnotationsTests\Fixtures\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationWithVarType
{

    /**
     * @var mixed
     */
    public $mixed;

    /**
     * @var boolean
     */
    public $boolean;

    /**
     * @var bool
     */
    public $bool;

    /**
     * @var float
     */
    public $float;

    /**
     * @var string
     */
    public $string;

    /**
     * @var integer
     */
    public $integer;

    /**
     * @var array
     */
    public $array;

    /**
     * @var Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll
     */
    public $annotation;

    /**
     * @var array<integer>
     */
    public $arrayOfIntegers;

    /**
     * @var string[]
     */
    public $arrayOfStrings;

    /**
     * @var array<Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll>
     */
    public $arrayOfAnnotations;

}