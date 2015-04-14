<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithInheritedMethodAnnotation extends ClassWithMethodAnnotation
{
    /**
     * @inheritdoc
     */
    public function methodWithAnnotation()
    {
    }

    public function anotherMethodWithAnnotation()
    {
        parent::anotherMethodWithAnnotation();
    }
}