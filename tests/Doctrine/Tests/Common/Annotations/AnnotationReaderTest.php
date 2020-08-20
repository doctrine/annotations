<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\SingleUseAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithFullPathUseStatement;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithImportedIgnoredAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithPHPCodeSnifferAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithPhpCsSuppressAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithPHPStanGenericsAnnotations;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtClassLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtMethodLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtPropertyLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedWithAlias;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

use function class_exists;
use function spl_autoload_register;
use function spl_autoload_unregister;

class AnnotationReaderTest extends AbstractReaderTest
{
    /**
     * @return AnnotationReader
     */
    protected function getReader(?DocParser $parser = null): Reader
    {
        return new AnnotationReader($parser);
    }

    public function testMethodAnnotationFromTrait(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('someMethod'));
        self::assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function testMethodAnnotationFromOverwrittenTrait(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(Fixtures\ClassOverwritesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        self::assertInstanceOf(Bar2\Autoload::class, $annotations[0]);
    }

    public function testPropertyAnnotationFromTrait(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('aProperty'));
        self::assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('traitProperty'));
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function testOmitNotRegisteredAnnotation(): void
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $reader = $this->getReader($parser);
        $ref    = new ReflectionClass(Fixtures\ClassWithNotRegisteredAnnotationUsed::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('methodWithNotRegisteredAnnotation'));
        self::assertEquals([], $annotations);
    }

    /**
     * @group 45
     * @runInSeparateProcess
     */
    public function testClassAnnotationIsIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(AnnotatedAtClassLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeClassAnnotationNamespace');

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    /**
     * @group 45
     * @runInSeparateProcess
     */
    public function testMethodAnnotationIsIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(AnnotatedAtMethodLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeMethodAnnotationNamespace');

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('test')));
    }

    /**
     * @group 45
     * @runInSeparateProcess
     */
    public function testPropertyAnnotationIsIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(AnnotatedAtPropertyLevel::class);

        $reader::addGlobalIgnoredNamespace('SomePropertyAnnotationNamespace');

        self::assertEmpty($reader->getPropertyAnnotations($ref->getProperty('property')));
    }

    /**
     * @group 244
     * @runInSeparateProcess
     */
    public function testAnnotationWithAliasIsIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(AnnotatedWithAlias::class);

        $reader::addGlobalIgnoredNamespace('SomePropertyAnnotationNamespace');

        self::assertEmpty($reader->getPropertyAnnotations($ref->getProperty('property')));
    }

    public function testClassWithFullPathUseStatement(): void
    {
        if (class_exists(SingleUseAnnotation::class, false)) {
            throw new LogicException(
                'The SingleUseAnnotation must not be used in other tests for this test to be useful.' .
                'If the class is already loaded then the code path that finds the class to load is not used and ' .
                'this test becomes useless.'
            );
        }

        $reader = $this->getReader();
        $ref    = new ReflectionClass(ClassWithFullPathUseStatement::class);

        $annotations = $reader->getClassAnnotations($ref);

        self::assertInstanceOf(SingleUseAnnotation::class, $annotations[0]);
    }

    public function testPhpCsSuppressAnnotationIsIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(ClassWithPhpCsSuppressAnnotation::class);

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('foo')));
    }

    public function testGloballyIgnoredAnnotationNotIgnored(): void
    {
        $reader     = $this->getReader();
        $class      = new ReflectionClass(Fixtures\ClassDDC1660::class);
        $testLoader = static function (string $className): bool {
            if ($className === 'since') {
                throw new InvalidArgumentException(
                    'Globally ignored annotation names should never be passed to an autoloader.'
                );
            }

            return false;
        };
        spl_autoload_register($testLoader, true, true);
        try {
            self::assertEmpty($reader->getClassAnnotations($class));
        } finally {
            spl_autoload_unregister($testLoader);
        }
    }

    public function testPHPCodeSnifferAnnotationsAreIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(ClassWithPHPCodeSnifferAnnotation::class);

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    public function testPHPStanGenericsAnnotationsAreIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(ClassWithPHPStanGenericsAnnotations::class);

        self::assertEmpty($reader->getClassAnnotations($ref));
        self::assertEmpty($reader->getPropertyAnnotations($ref->getProperty('bar')));
        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('foo')));

        $this->expectException('\Doctrine\Common\Annotations\AnnotationException');
        $this->expectExceptionMessage(
            '[Semantical Error] The annotation "@Template" in method' .
            ' Doctrine\Tests\Common\Annotations\Fixtures\ClassWithPHPStanGenericsAnnotations' .
            '::twigTemplateFunctionName() was never imported.'
        );
        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('twigTemplateFunctionName')));
    }

    public function testImportedIgnoredAnnotationIsStillIgnored(): void
    {
        $reader = $this->getReader();
        $ref    = new ReflectionClass(ClassWithImportedIgnoredAnnotation::class);

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('something')));
    }
}
