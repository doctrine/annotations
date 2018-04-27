<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetClass;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Annotations\Fixtures\AnnotationTargetPropertyMethod;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithValidAnnotationTarget
{

    /**
     * @AnnotationTargetPropertyMethod("Some data")
     */
    public $foo;


    /**
     * @AnnotationTargetAll("Some data",name="Some name")
     */
    public $name;

    /**
     * @AnnotationTargetPropertyMethod("Some data",name="Some name")
     */
    public function someFunction()
    {

    }


    /**
     * @AnnotationTargetAll(@AnnotationTargetAnnotation)
     */
    public $nested;

}
