<?php

namespace Doctrine\Tests\Annotations\Ticket;

use Doctrine\Annotations\AnnotationException;
use Doctrine\Tests\Annotations\Fixtures\Controller;
use Doctrine\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

/**
 * @group
 */
class DCOM55Test extends TestCase
{
    public function testIssue() : void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            sprintf(
                '[Semantical Error] The class "%s" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "%s". If it is indeed no annotation, then you need to add @IgnoreAnnotation("Controller") to the _class_ doc comment of class %s.',
                Controller::class,
                Controller::class,
                Dummy::class
            )
        );

        $class = new \ReflectionClass(__NAMESPACE__ . '\\Dummy');
        $reader = new AnnotationReader();
        $reader->getClassAnnotations($class);
    }

    public function testAnnotation() : void
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\\DCOM55Consumer');
        $reader = new AnnotationReader();
        $annots = $reader->getClassAnnotations($class);

        self::assertCount(1, $annots);
        self::assertInstanceOf(__NAMESPACE__.'\\DCOM55Annotation', $annots[0]);
    }

    public function testParseAnnotationDocblocks() : void
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\\DCOM55Annotation');
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
