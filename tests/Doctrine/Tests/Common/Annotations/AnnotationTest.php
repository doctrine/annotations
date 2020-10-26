<?php

namespace Doctrine\Tests\Common\Annotations;

use BadMethodCallException;
use Doctrine\Common\Annotations\Annotation;
use PHPUnit\Framework\TestCase;

use function sprintf;

final class AnnotationTest extends TestCase
{
    public function testMagicGetThrowsBadMethodCallException(): void
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

         // @phpstan-ignore-next-line This is expected not to exist
        $annotation->{$name};
    }

    public function testMagicSetThrowsBadMethodCallException(): void
    {
        $name = 'foo';

        $annotation = new Annotation([]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            "Unknown property '%s' on annotation '%s'.",
            $name,
            Annotation::class
        ));

         // @phpstan-ignore-next-line This is expected not to exist
        $annotation->{$name} = 9001;
    }
}
