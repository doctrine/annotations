<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\SimpleAnnotationReader;

class SimpleAnnotationReaderTest extends AbstractReaderTest
{

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testImportDetectsNotImportedAnnotation()
    {

    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testImportDetectsNonExistentAnnotation()
    {

    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testClassWithInvalidAnnotationTargetAtClassDocBlock()
    {

    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testClassWithInvalidAnnotationTargetAtPropertyDocBlock()
    {

    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testClassWithInvalidNestedAnnotationTargetAtPropertyDocBlock()
    {

    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testClassWithInvalidAnnotationTargetAtMethodDocBlock()
    {

    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\InvalidAnnotationException
     */
    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        parent::testInvalidAnnotationUsageButIgnoredClass();
    }

    public function testIncludeIgnoreAnnotation()
    {
        $this->markTestSkipped('The simplified annotation reader would always autoload annotations');
    }

    /**
     * @group DDC-1660
     * @group regression
     *
     * Contrary to the behavior of the default annotation reader, @version is not ignored
     */
    public function testInvalidAnnotationButIgnored()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassDDC1660');

        $this->assertTrue(class_exists('Doctrine\AnnotationsTests\Fixtures\Annotation\Version'));

        $this->assertCount(1, $reader->getClassAnnotations($class));
        $this->assertCount(1, $reader->getMethodAnnotations($class->getMethod('bar')));
        $this->assertCount(1, $reader->getPropertyAnnotations($class->getProperty('foo')));
    }

    protected function getReader()
    {
        $config = $this->config;
        $reader = new SimpleAnnotationReader($this->config);

        $reader->addNamespace('Doctrine\AnnotationsTests\Fixtures\Reader');
        $reader->addNamespace('Doctrine\AnnotationsTests\Fixtures\Annotation');

        return $reader;
    }
}