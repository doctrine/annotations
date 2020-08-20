<?php

namespace Doctrine\Tests\Common\Annotations\Ticket;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group
 */
class DCOM141Test extends TestCase
{
    public function testAnnotationPrefixed(): void
    {
        $class  = new ReflectionClass(DCOM141ConsumerPrefixed::class);
        $reader = new AnnotationReader();
        $annots = $reader->getClassAnnotations($class);

        self::assertCount(1, $annots);
        self::assertInstanceOf(DCOM141Annotation::class, $annots[0]);
        self::assertEquals('SimpleXMLElement', $annots[0]->classPath);
    }

    public function testAnnotationNotPrefixed(): void
    {
        $class  = new ReflectionClass(DCOM141ConsumerNotPrefixed::class);
        $reader = new AnnotationReader();
        $annots = $reader->getClassAnnotations($class);

        self::assertCount(1, $annots);
        self::assertInstanceOf(DCOM141Annotation::class, $annots[0]);
        self::assertEquals('SimpleXMLElement', $annots[0]->classPath);
    }
}

/**
 * @Annotation
 */
class DCOM141Annotation
{
    /** @var mixed */
    public $classPath;
}

/**
 * @DCOM141Annotation(\SimpleXMLElement::class)
 */
class DCOM141ConsumerPrefixed
{
}

/**
 * @DCOM141Annotation(SimpleXMLElement::class)
 */
class DCOM141ConsumerNotPrefixed
{
}
