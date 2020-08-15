<?php

namespace Doctrine\Tests\Common\Annotations\Ticket;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Tests\Common\Annotations\Fixtures\Controller;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group
 */
class DCOM55Test extends TestCase
{
    public function testIssue(): void
    {
        $class  = new ReflectionClass(__NAMESPACE__ . '\\Dummy');
        $reader = new AnnotationReader();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            '[Semantical Error] The class "Doctrine\Tests\Common\Annotations\Fixtures\Controller"' .
            ' is not annotated with @Annotation.
Are you sure this class can be used as annotation?
If so, then you need to add @Annotation to the _class_ doc comment of' .
            ' "Doctrine\Tests\Common\Annotations\Fixtures\Controller".
If it is indeed no annotation, then you need to add @IgnoreAnnotation("Controller")' .
            ' to the _class_ doc comment of class Doctrine\Tests\Common\Annotations\Ticket\Dummy.'
        );
        $reader->getClassAnnotations($class);
    }

    public function testAnnotation(): void
    {
        $class  = new ReflectionClass(__NAMESPACE__ . '\\DCOM55Consumer');
        $reader = new AnnotationReader();
        $annots = $reader->getClassAnnotations($class);

        self::assertCount(1, $annots);
        self::assertInstanceOf(__NAMESPACE__ . '\\DCOM55Annotation', $annots[0]);
    }

    public function testParseAnnotationDocblocks(): void
    {
        $class  = new ReflectionClass(__NAMESPACE__ . '\\DCOM55Annotation');
        $reader = new AnnotationReader();
        $annots = $reader->getClassAnnotations($class);

        self::assertEmpty($annots);
    }
}

/**
 * @Controller
 */
class Dummy
{
}

/**
 * @Annotation
 */
class DCOM55Annotation
{
}

/**
 * @DCOM55Annotation
 */
class DCOM55Consumer
{
}
