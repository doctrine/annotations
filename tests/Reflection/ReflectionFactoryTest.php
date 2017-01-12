<?php

namespace Doctrine\AnnotationsTests\Reflection;

use Doctrine\AnnotationsTests\TestCase;
use Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum;

use Doctrine\Annotations\Parser\PhpParser;
use Doctrine\Annotations\Reflection\ReflectionClass;
use Doctrine\Annotations\Reflection\ReflectionFunction;
use Doctrine\Annotations\Reflection\ReflectionMethod;
use Doctrine\Annotations\Reflection\ReflectionProperty;
use Doctrine\Annotations\Reflection\ReflectionFactory;

class ReflectionFactoryTest extends TestCase
{
    /**
     * @return \Doctrine\Annotations\Reflection\ReflectionFactory
     */
    public function createReflectionFactory() : ReflectionFactory
    {
        $parser  = new PhpParser();
        $factory = new ReflectionFactory($parser);

        return $factory;
    }

    public function testGetReflectionClass()
    {
        $factory    = $this->createReflectionFactory();
        $reflection = $factory->getReflectionClass(ClassWithAnnotationEnum::CLASS);

        $this->assertInstanceOf('\ReflectionClass', $reflection);
        $this->assertInstanceOf(ReflectionClass::CLASS, $reflection);

        $this->assertSame($reflection, $factory->getReflectionClass(ClassWithAnnotationEnum::CLASS));
    }

    public function testGetReflectionMethod()
    {
        $factory    = $this->createReflectionFactory();
        $reflection = $factory->getReflectionMethod(ClassWithAnnotationEnum::CLASS, 'bar');

        $this->assertInstanceOf('\ReflectionMethod', $reflection);
        $this->assertInstanceOf(ReflectionMethod::CLASS, $reflection);

        $this->assertSame($reflection, $factory->getReflectionMethod(ClassWithAnnotationEnum::CLASS, 'bar'));
    }

    public function testGetReflectionProperty()
    {
        $factory    = $this->createReflectionFactory();
        $reflection = $factory->getReflectionProperty(ClassWithAnnotationEnum::CLASS, 'foo');

        $this->assertInstanceOf('\ReflectionProperty', $reflection);
        $this->assertInstanceOf(ReflectionProperty::CLASS, $reflection);

        $this->assertSame($reflection, $factory->getReflectionProperty(ClassWithAnnotationEnum::CLASS, 'foo'));
    }

    public function testGetReflectionFunction()
    {
        $factory    = $this->createReflectionFactory();
        $reflection = $factory->getReflectionFunction('Doctrine\AnnotationsTests\Fixtures\annotation_enum_function');

        $this->assertInstanceOf('\ReflectionFunction', $reflection);
        $this->assertInstanceOf(ReflectionFunction::CLASS, $reflection);

        $this->assertSame($reflection, $factory->getReflectionFunction('Doctrine\AnnotationsTests\Fixtures\annotation_enum_function'));
    }
}
