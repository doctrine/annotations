<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetAll;

/**
 * @AnnotationTargetClass("Some data")
 */
class ClassWithValidAnnotationTarget
{

    /**
     * @AnnotationTargetAll("Some data")
     */
    public $foo;
    
    
    /**
     * @AnnotationTargetAll("Some data",name="Some name")
     */
    public $name;
    
    /**
     * @AnnotationTargetAll("Some data",name="Some name")
     */
    public function someFunction()
    {
        
    }

}