<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Common\Annotations\Annotation\Inherited;

class ClassWithInheritedMethodAnnotation extends ClassWithMethodAnnotation
{
    /**
     * @Inherited
     */
    public function methodWithAnnotation()
    {
    }

    public function anotherMethodWithAnnotation()
    {
        parent::anotherMethodWithAnnotation();
    }
}