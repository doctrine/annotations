<?php

namespace Doctrine\AnnotationsTests\Reflection;

use Doctrine\AnnotationsTests\TestCase;
use Doctrine\AnnotationsTests\Fixtures\ClassUsesTrait;
use Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum;

use Doctrine\Annotations\Parser\PhpParser;
use Doctrine\Annotations\Reflection\ReflectionClass;
use Doctrine\Annotations\Reflection\ReflectionMethod;

class ReflectionMethodTest extends TestCase
{
    /**
     * @param string $class
     * @param string $method
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionMethod
     */
    public function createReflectionMethod(string $class, string $method) : ReflectionMethod
    {
        $parser     = new PhpParser();
        $reflection = new ReflectionMethod($class, $method, $parser);

        return $reflection;
    }

    public function testGetImports()
    {
        $reflection = $this->createReflectionMethod(ClassWithAnnotationEnum::CLASS, 'bar');
        $imports    = $reflection->getImports();

        $this->assertArrayHasKey('annotationenum', $imports);
        $this->assertEquals(AnnotationEnum::CLASS, $imports['annotationenum']);
        $this->assertEquals($imports, $reflection->getImports());
    }

    public function testGetImportsFromTrait()
    {
        $traitMethodRef = $this->createReflectionMethod(ClassUsesTrait::CLASS, 'traitMethod');
        $someMethodRef  = $this->createReflectionMethod(ClassUsesTrait::CLASS, 'someMethod');

        $someMethodImports  = $someMethodRef->getImports();
        $traitMethodImports = $traitMethodRef->getImports();

        $this->assertArrayHasKey('autoload', $someMethodImports);
        $this->assertArrayHasKey('autoload', $traitMethodImports);

        $this->assertEquals($someMethodImports, $someMethodRef->getImports());
        $this->assertEquals($traitMethodImports, $traitMethodRef->getImports());

        $this->assertEquals('Doctrine\AnnotationsTests\Bar\Autoload', $someMethodImports['autoload']);
        $this->assertEquals('Doctrine\AnnotationsTests\Fixtures\Annotation\Autoload', $traitMethodImports['autoload']);
    }

    public function testGetReflectionClass()
    {
        $reflection = $this->createReflectionMethod(ClassWithAnnotationEnum::CLASS, 'bar');
        $class      = $reflection->getDeclaringClass();

        $this->assertInstanceOf(ReflectionClass::CLASS, $class);
        $this->assertSame($class, $reflection->getDeclaringClass());
        $this->assertEquals(ClassWithAnnotationEnum::CLASS, $class->getName());
    }
}
