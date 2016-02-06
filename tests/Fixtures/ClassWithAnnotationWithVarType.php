<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAnnotation;

class ClassWithAnnotationWithVarType
{
    /**
     * @AnnotationWithVarType(string = "String Value")
     */
    public $foo;

    /**
     * @AnnotationWithVarType(annotation = @AnnotationTargetAll)
     */
    public function bar(){}


    /**
     * @AnnotationWithVarType(string = 123)
     */
    public $invalidProperty;

    /**
     * @AnnotationWithVarType(annotation = @AnnotationTargetAnnotation)
     */
    public function invalidMethod(){}
}