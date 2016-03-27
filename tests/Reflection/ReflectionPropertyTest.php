<?php

namespace Doctrine\AnnotationsTests\Reflection;

use Doctrine\AnnotationsTests\TestCase;
use Doctrine\AnnotationsTests\Fixtures\ClassUsesTrait;
use Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum;

use Doctrine\Annotations\Parser\PhpParser;
use Doctrine\Annotations\Reflection\ReflectionClass;
use Doctrine\Annotations\Reflection\ReflectionProperty;

class ReflectionPropertyTest extends TestCase
{
    /**
     * @param string $class
     * @param string $property
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionProperty
     */
    public function createReflectionProperty(string $class, string $property) : ReflectionProperty
    {
        $parser     = new PhpParser();
        $reflection = new ReflectionProperty($class, $property, $parser);

        return $reflection;
    }

    public function testGetImports()
    {
        $reflection = $this->createReflectionProperty(ClassWithAnnotationEnum::CLASS, 'foo');
        $imports    = $reflection->getImports();

        $this->assertArrayHasKey('annotationenum', $imports);
        $this->assertEquals(AnnotationEnum::CLASS, $imports['annotationenum']);
        $this->assertEquals($imports, $reflection->getImports());
    }

    public function testGetImportsFromTrait()
    {
        $traitPropertyRef = $this->createReflectionProperty(ClassUsesTrait::CLASS, 'traitProperty');
        $somePropertyRef  = $this->createReflectionProperty(ClassUsesTrait::CLASS, 'aProperty');

        $somePropertyImports  = $somePropertyRef->getImports();
        $traitPropertyImports = $traitPropertyRef->getImports();

        $this->assertArrayHasKey('autoload', $somePropertyImports);
        $this->assertArrayHasKey('autoload', $traitPropertyImports);

        $this->assertEquals($somePropertyImports, $somePropertyRef->getImports());
        $this->assertEquals($traitPropertyImports, $traitPropertyRef->getImports());

        $this->assertEquals('Doctrine\AnnotationsTests\Bar\Autoload', $somePropertyImports['autoload']);
        $this->assertEquals('Doctrine\AnnotationsTests\Fixtures\Annotation\Autoload', $traitPropertyImports['autoload']);
    }

    public function testGetReflectionClass()
    {
        $reflection = $this->createReflectionProperty(ClassWithAnnotationEnum::CLASS, 'foo');
        $class      = $reflection->getDeclaringClass();

        $this->assertInstanceOf(ReflectionClass::CLASS, $class);
        $this->assertSame($class, $reflection->getDeclaringClass());
        $this->assertEquals(ClassWithAnnotationEnum::CLASS, $class->getName());
    }
}
