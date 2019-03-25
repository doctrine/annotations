<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\AnnotationEnum;

class ClassWithAnnotationEnum
{
    /**
     * @AnnotationEnum(AnnotationEnum::ONE)
     */
    public $foo;

    /**
     * @AnnotationEnum("TWO")
     */
    public function bar() : void{}


    /**
     * @AnnotationEnum("FOUR")
     */
    public $invalidProperty;

    /**
     * @AnnotationEnum(5)
     */
    public function invalidMethod() : void{}
}
