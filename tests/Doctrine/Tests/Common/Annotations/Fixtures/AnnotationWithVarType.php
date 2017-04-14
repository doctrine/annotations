<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll as RenamedAnnotationTargetAll;
use Doctrine\Tests\Common;
use Doctrine\Tests\Common as RenamedCommon;

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
     * @var \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll
     */
    public $annotation;

    /**
     * @var AnnotationTargetAll
     */
    public $unqualifiedAnnotation;

    /**
     * @var RenamedAnnotationTargetAll
     */
    public $renamedAnnotation;

    /**
     * @var Common\Annotations\Fixtures\AnnotationTargetAll
     */
    public $partiallyNamedAnnotation;

    /**
     * @var RenamedCommon\Annotations\Fixtures\AnnotationTargetAll
     */
    public $partiallyNamedAndRenamedAnnotation;

    /**
     * @var array<integer>
     */
    public $arrayOfIntegers;

    /**
     * @var string[]
     */
    public $arrayOfStrings;

    /**
     * @var \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll[]
     */
    public $arrayOfAnnotations;

    /**
     * @var AnnotationTargetAll[]
     */
    public $arrayOfUnqualifiedAnnotations;

    /**
     * @var RenamedAnnotationTargetAll[]
     */
    public $arrayOfRenamedAnnotations;

    /**
     * @var Common\Annotations\Fixtures\AnnotationTargetAll[]
     */
    public $arrayOfPartiallyNamedAnnotations;

    /**
     * @var RenamedCommon\Annotations\Fixtures\AnnotationTargetAll[]
     */
    public $arrayOfPartiallyNamedAndRenamedAnnotations;

}
