<?php

namespace Doctrine\Tests\Common\Annotations;

// make sure this is loaded first
require_once __DIR__.'/ReaderTest.php';

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\DoctrineReader;

class DoctrineReaderTest extends AbstractReaderTest
{
    public function setUp()
    {
        if (!class_exists('Doctrine\Common\Annotations\AnnotationReader')) {
            $this->markTestSkipped('Doctrine Common is not available.');
        }

        parent::setUp();
    }

    public function testDoctrineGetClassAnnotations()
    {
        $reader = $this->getReader();

        $annotations = $reader->getClassAnnotations(new \ReflectionClass('Doctrine\Tests\Common\Annotations\DoctrineReaderTestClass'));
        $this->assertEquals(1, count($annotations));
        $this->assertTrue(isset($annotations['TopLevelAnnotation']));
    }

    public function testDoctrineGetPropertyAnnotations()
    {
        $reader = $this->getReader();

        $annotations = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\DoctrineReaderTestClass', 'foo'));
        $this->assertEquals(1, count($annotations));
        $this->assertTrue(isset($annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']));
        $this->assertEquals('foo', $annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']->dummyValue);
    }

    public function testDoctrineGetMethodAnnotations()
    {
        $reader = $this->getReader();

        $annotations = $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\Tests\Common\Annotations\DoctrineReaderTestClass', 'bar'));
        $this->assertEquals(1, count($annotations));
        $this->assertTrue(isset($annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']));
        $this->assertEquals('bar', $annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']->dummyValue);
    }

    protected function getReader()
    {
        return new DoctrineReader(new Reader());
    }
}

/**
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @\TopLevelAnnotation
 */
class DoctrineReaderTestClass
{
    /**
     * @DummyAnnotation(dummyValue = "foo")
     * @var string
     */
    private $foo;

    /**
     * @DummyAnnotation(dummyValue = "bar")
     */
    private function bar() {}
}