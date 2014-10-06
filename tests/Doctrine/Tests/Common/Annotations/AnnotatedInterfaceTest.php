<?php


namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassExtendsInterfaceAnnotated;

/**
 * Class AnnotatedInterfaceTest
 * @package Doctrine\Tests\Common\Annotations
 */
class AnnotatedInterfaceTest extends \PHPUnit_Framework_TestCase
{
    protected function getReader()
    {
        return new AnnotationReader();
    }

    public function testInstantiatingClassWithAnnotatedInterface()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassExtendsInterfaceAnnotated');

        $annotations = $reader->getClassAnnotations($ref);
        // the class now has the interface annotation
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass', $annotations[0]);
        // and his own annotation too
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType', $annotations[1]);
    }
}
