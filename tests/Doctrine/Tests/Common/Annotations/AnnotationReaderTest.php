<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\SingleUseAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithFullPathUseStatement;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtClassLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtClassLevelWithTrait;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtMethodLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtPropertyLevel;

class AnnotationReaderTest extends AbstractReaderTest
{
    /**
     * @param DocParser|null $parser
     * @return AnnotationReader
     */
    protected function getReader(DocParser $parser = null): AnnotationReader
    {
        return new AnnotationReader($parser);
    }

    public function testMethodAnnotationFromTrait() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('someMethod'));
        self::assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $traitAnnotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $traitAnnotations[0]);

        self::assertNotEquals($annotations[0], $traitAnnotations[0]);
    }

    public function testMethodAnnotationChooseTheFirstOneWhenOverwrittenTraitHappen() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassOverwritesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        self::assertInstanceOf(Bar2\Autoload::class, $annotations[0]);
    }

    public function testPropertyAnnotationFromTrait() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('aProperty'));
        self::assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('traitProperty'));
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function testOmitNotRegisteredAnnotation() : void
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $reader = $this->getReader($parser);
        $ref = new \ReflectionClass(Fixtures\ClassWithNotRegisteredAnnotationUsed::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('methodWithNotRegisteredAnnotation'));
        self::assertEquals(array(), $annotations);
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testClassAnnotationIsIgnored() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtClassLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeClassAnnotationNamespace');

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testMethodAnnotationIsIgnored() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtMethodLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeMethodAnnotationNamespace');

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('test')));
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testMethodAnnotationIsIgnoredWithTrait() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtClassLevelWithTrait::class);

        $reader::addGlobalIgnoredNamespace('IgnoreNamespaceTrait');

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('test')));
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testPropertyAnnotationIsIgnored() : void
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtPropertyLevel::class);

        $reader::addGlobalIgnoredNamespace('SomePropertyAnnotationNamespace');

        self::assertEmpty($reader->getPropertyAnnotations($ref->getProperty('property')));
    }

    public function testClassWithFullPathUseStatement() :void
    {
        if (class_exists(SingleUseAnnotation::class, false)) {
            throw new \LogicException(
                'The SingleUseAnnotation must not be used in other tests for this test to be useful.' .
                'If the class is already loaded then the code path that finds the class to load is not used and ' .
                'this test becomes useless.'
            );
        }

        $reader = $this->getReader();
        $ref = new \ReflectionClass(ClassWithFullPathUseStatement::class);

        $annotations = $reader->getClassAnnotations($ref);

        self::assertInstanceOf(SingleUseAnnotation::class,$annotations[0]);
    }
}
