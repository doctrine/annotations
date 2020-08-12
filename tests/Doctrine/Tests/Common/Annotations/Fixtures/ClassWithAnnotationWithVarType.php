<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType;

class ClassWithAnnotationWithVarType
{
    /** @AnnotationWithVarType(string = "String Value") */
    public $foo;

    /**
     * @AnnotationWithVarType(annotation = @AnnotationTargetAll)
     */
    public function bar(): void
    {
    }

    /** @AnnotationWithVarType(string = 123) */
    public $invalidProperty;

    /**
     * @AnnotationWithVarType(annotation = @AnnotationTargetAnnotation)
     */
    public function invalidMethod(): void
    {
    }
}
