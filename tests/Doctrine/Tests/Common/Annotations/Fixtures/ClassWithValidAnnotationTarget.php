<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetPropertyMethod;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetConstant;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithValidAnnotationTarget
{
    /**
     * @AnnotationTargetConstant()
     */
    public const SOME_CONSTANT = 'someConstant';

    /** @AnnotationTargetPropertyMethod("Some data") */
    public $foo;


    /** @AnnotationTargetAll("Some data",name="Some name") */
    public $name;

    /**
     * @AnnotationTargetPropertyMethod("Some data",name="Some name")
     */
    public function someFunction(): void
    {
    }

    /** @AnnotationTargetAll(@AnnotationTargetAnnotation) */
    public $nested;
}
