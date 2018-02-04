<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation;
use PHPUnit\Framework\TestCase;

final class AnnotationTest extends TestCase
{
    public function testMagicGetThrowsBadMethodCallException()
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

        $annotation->{$name};
    }

    public function testMagicSetThrowsBadMethodCallException()
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

        $annotation->{$name} = 9001;
    }
}
