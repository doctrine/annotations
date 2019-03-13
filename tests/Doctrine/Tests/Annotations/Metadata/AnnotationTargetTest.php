<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata;

use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\Exception\InvalidAnnotationTarget;
use PHPUnit\Framework\TestCase;
use function sprintf;

final class AnnotationTargetTest extends TestCase
{
    public function testConstructor() : void
    {
        self::assertSame(AnnotationTarget::TARGET_ALL, (new AnnotationTarget(AnnotationTarget::TARGET_ALL))->unwrap());
    }

    public function testAllIncludesEverything() : void
    {
        self::assertTrue((AnnotationTarget::TARGET_ALL & AnnotationTarget::TARGET_CLASS) !== 0, 'class in all');
        self::assertTrue((AnnotationTarget::TARGET_ALL & AnnotationTarget::TARGET_METHOD) !== 0, 'method in all');
        self::assertTrue((AnnotationTarget::TARGET_ALL & AnnotationTarget::TARGET_PROPERTY) !== 0, 'property in all');
        self::assertTrue((AnnotationTarget::TARGET_ALL & AnnotationTarget::TARGET_ANNOTATION) !== 0, 'annotation in all');
    }

    public function testStaticFactories() : void
    {
        self::assertSame(AnnotationTarget::TARGET_CLASS, AnnotationTarget::class()->unwrap());
        self::assertSame(AnnotationTarget::TARGET_METHOD, AnnotationTarget::method()->unwrap());
        self::assertSame(AnnotationTarget::TARGET_PROPERTY, AnnotationTarget::property()->unwrap());
        self::assertSame(AnnotationTarget::TARGET_ANNOTATION, AnnotationTarget::annotation()->unwrap());
        self::assertSame(AnnotationTarget::TARGET_ALL, AnnotationTarget::all()->unwrap());
    }

    /**
     * @dataProvider accessorsProvider()
     */
    public function testAccessors(AnnotationTarget $target, int $raw) : void
    {
        self::assertSame(($raw & AnnotationTarget::TARGET_CLASS) !== 0, $target->targetsClass());
        self::assertSame(($raw & AnnotationTarget::TARGET_METHOD) !== 0, $target->targetsMethod());
        self::assertSame(($raw & AnnotationTarget::TARGET_PROPERTY) !== 0, $target->targetsProperty());
        self::assertSame(($raw & AnnotationTarget::TARGET_ANNOTATION) !== 0, $target->targetsAnnotation());
    }

    /**
     * @dataProvider describeProvider()
     */
    public function testDescribe(AnnotationTarget $target, string $described) : void
    {
        self::assertSame($described, $target->describe());
    }

    /**
     * @dataProvider invalidTargetsProvider()
     */
    public function testInvalidTargetBitmask(int $target) : void
    {
        $this->expectException(InvalidAnnotationTarget::class);
        $this->expectExceptionMessage(sprintf('Annotation target "%d" is not valid bitmask of allowed targets.', $target));

        new AnnotationTarget($target);
    }

    /**
     * @return (AnnotationTarget|int)[][]
     */
    public function accessorsProvider() : iterable
    {
        yield [AnnotationTarget::class(), AnnotationTarget::TARGET_CLASS];
        yield [AnnotationTarget::method(), AnnotationTarget::TARGET_METHOD];
        yield [AnnotationTarget::property(), AnnotationTarget::TARGET_PROPERTY];
        yield [AnnotationTarget::annotation(), AnnotationTarget::TARGET_ANNOTATION];
        yield [AnnotationTarget::all(), AnnotationTarget::TARGET_ALL];
    }

    /**
     * @return (AnnotationTarget|int)[][]
     */
    public function describeProvider() : iterable
    {
        yield [AnnotationTarget::class(), 'CLASS'];
        yield [AnnotationTarget::method(), 'METHOD'];
        yield [AnnotationTarget::property(), 'PROPERTY'];
        yield [AnnotationTarget::annotation(), 'ANNOTATION'];
        yield [AnnotationTarget::all(), 'ALL'];
        yield [
            new AnnotationTarget(AnnotationTarget::TARGET_CLASS | AnnotationTarget::TARGET_ANNOTATION),
            'CLASS, ANNOTATION',
        ];
    }

    /**
     * @return int[][]
     */
    public function invalidTargetsProvider() : iterable
    {
        yield [-1];
        yield [AnnotationTarget::TARGET_ALL + 1];
    }
}
