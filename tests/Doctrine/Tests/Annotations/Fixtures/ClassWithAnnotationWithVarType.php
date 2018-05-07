<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\AnnotationWithVarType;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAnnotation;

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
