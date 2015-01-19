<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderTest extends AbstractReaderTest
{
    protected function getReader()
    {
        return new AnnotationReader();
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

    public function testClassAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\TestInvalidClassAnnotationClass');

        $reader::addGlobalIgnoredNamespace('SomeClassAnnotationNamespace');

        $annotations = $reader->getClassAnnotations($ref);

        $this->assertEquals(0, count($annotations));
    }

    public function testMethodAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\TestInvalidMethodAnnotationClass');

        $reader::addGlobalIgnoredNamespace('SomeMethodAnnotationNamespace');

        $annotations = $reader->getMethodAnnotations($ref->getMethod('test'));

        $this->assertEquals(0, count($annotations));
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
