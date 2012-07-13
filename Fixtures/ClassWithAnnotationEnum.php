<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum;

class ClassWithAnnotationEnum
{
    /**
     * AnnotationEnum(AnnotationEnum::ONE)
     */
    public $foo;

    /**
     * AnnotationEnum("TOW")
     */
    public function bar(){}


    /**
     * @AnnotationEnum("FOUR")
     */
    public $invalidProperty;

    /**
     * @AnnotationEnum({"ONE"})
     */
    public function invalidMethod(){}
}