<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderTest extends AbstractReaderTest
{
    protected function getReader()
    {
        return new AnnotationReader();
    }

    public function testAnnotationFromTrait()
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

    public function testAnnotationFromOverwrittenTrait()
    {
        if (PHP_VERSION_ID < 50400) {
           $this->markTestSkipped('This test requires PHP 5.4 or later.');
        }

        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\ClassOverwritesTrait');

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar2\Autoload', $annotations[0]);
    }
}