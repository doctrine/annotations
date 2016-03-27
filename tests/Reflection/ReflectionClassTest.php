<?php

namespace Doctrine\AnnotationsTests\Reflection;

use Doctrine\AnnotationsTests\TestCase;
use Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum;

use Doctrine\Annotations\Parser\PhpParser;
use Doctrine\Annotations\Reflection\ReflectionClass;

class ReflectionClassTest extends TestCase
{
    /**
     * @param string $class
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionClass
     */
    public function createReflectionClass(string $class) : ReflectionClass
    {
        $parser     = new PhpParser();
        $reflection = new ReflectionClass($class, $parser);

        return $reflection;
    }

    public function testGetImports()
    {
        $reflection = $this->createReflectionClass(ClassWithAnnotationEnum::CLASS);
        $imports    = $reflection->getImports();

        $this->assertArrayHasKey('annotationenum', $imports);
        $this->assertEquals(AnnotationEnum::CLASS, $imports['annotationenum']);
        $this->assertEquals($imports, $reflection->getImports());
    }
}
