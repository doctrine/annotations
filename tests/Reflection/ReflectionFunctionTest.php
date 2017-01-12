<?php

namespace Doctrine\AnnotationsTests\Reflection;

use Doctrine\Annotations\Reflection\ReflectionFunction;
use Doctrine\AnnotationsTests\TestCase;
use Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum;

use Doctrine\Annotations\Parser\PhpParser;

class ReflectionFunctionTest extends TestCase
{
    /**
     * @param string $function
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionFunction
     */
    public function createReflectionFunction(string $function) : ReflectionFunction
    {
        $parser     = new PhpParser();
        $reflection = new ReflectionFunction($function, $parser);

        return $reflection;
    }

    public function testGetImports()
    {
        $reflection = $this->createReflectionFunction('Doctrine\AnnotationsTests\Fixtures\dummy_function');
        $imports    = $reflection->getImports();

        $this->assertArrayHasKey('annotationenum', $imports);
        $this->assertEquals(AnnotationEnum::CLASS, $imports['annotationenum']);
        $this->assertEquals($imports, $reflection->getImports());
    }
}
