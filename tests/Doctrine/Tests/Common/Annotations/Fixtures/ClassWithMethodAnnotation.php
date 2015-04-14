<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithMethodAnnotation
{
    /**
     * @AnnotationTargetMethod
     */
    public function methodWithAnnotation(){}

    /**
     * @AnnotationTargetMethod
     */
    public function anotherMethodWithAnnotation(){}
}