<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtClassLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtMethodLevel;

class AnnotationReaderTest extends AbstractReaderTest
{
    /**
     * @param DocParser|null $parser
     * @return AnnotationReader
     */
    protected function getReader(DocParser $parser = null)
    {
        return new AnnotationReader($parser);
    }

    public function testMethodAnnotationFromTrait()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('This test requires PHP 5.4 or later.');
        }

        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassUsesTrait');

        $annotations = $reader->getMethodAnnotations($ref->getMethod('someMethod'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar\Autoload', $annotations[0]);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload', $annotations[0]);
    }

    public function testMethodAnnotationFromOverwrittenTrait()
    {
        if (PHP_VERSION_ID < 50400) {
           $this->markTestSkipped('This test requires PHP 5.4 or later.');
        }

        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassOverwritesTrait');

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar2\Autoload', $annotations[0]);
    }

    public function testPropertyAnnotationFromTrait()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('This test requires PHP 5.4 or later.');
        }

        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassUsesTrait');

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('aProperty'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar\Autoload', $annotations[0]);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('traitProperty'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload', $annotations[0]);
    }

    public function testOmitNotRegisteredAnnotation()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $reader = $this->getReader($parser);
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassWithNotRegisteredAnnotationUsed');

        $annotations = $reader->getMethodAnnotations($ref->getMethod('methodWithNotRegisteredAnnotation'));
        $this->assertEquals(array(), $annotations);
    }

    public function testClassAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtClassLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeClassAnnotationNamespace');

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    public function testMethodAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtMethodLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeMethodAnnotationNamespace');

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('test')));
    }

    public function testPropertyAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\TestInvalidPropertyAnnotationClass');

        $reader::addGlobalIgnoredNamespace('SomePropertyAnnotationNamespace');

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('field'));

        $this->assertEquals(0, count($annotations));
    }
}

/**
 * @SomeClassAnnotationNamespace\Subnamespace\Name
 */
class TestInvalidClassAnnotationClass {}

class TestInvalidMethodAnnotationClass {
    /**
     * @SomeMethodAnnotationNamespace\Subnamespace\Name
     */
    public function test() {}
}

class TestInvalidPropertyAnnotationClass {
    /**
     * @SomePropertyAnnotationNamespace\Subnamespace\Name
     */
    private $field;
}
