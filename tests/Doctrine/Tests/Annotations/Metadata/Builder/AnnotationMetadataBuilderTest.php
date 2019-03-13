<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\Builder\AnnotationMetadataBuilder;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use PHPUnit\Framework\TestCase;

final class AnnotationMetadataBuilderTest extends TestCase
{
    public function testDefaults() : void
    {
        $metadata = (new AnnotationMetadataBuilder('Foo'))->build();

        self::assertSame('Foo', $metadata->getName());
        self::assertSame(AnnotationTarget::TARGET_ALL, $metadata->getTarget()->unwrap());
        self::assertFalse($metadata->usesConstructor());
        self::assertSame([], $metadata->getProperties());
        self::assertNull($metadata->getDefaultProperty());
    }

    public function testBuilding() : void
    {
        $propertyA = new PropertyMetadata('a', ['type' => 'string'], true, true);
        $propertyB = new PropertyMetadata('b', ['type' => 'boolean']);

        $metadata = (new AnnotationMetadataBuilder('Foo'))
            ->withTarget(AnnotationTarget::class())
            ->withUsingConstructor()
            ->withProperty($propertyA)
            ->withProperty($propertyB)
            ->build();

        self::assertSame('Foo', $metadata->getName());
        self::assertSame(AnnotationTarget::TARGET_CLASS, $metadata->getTarget()->unwrap());
        self::assertTrue($metadata->usesConstructor());
        self::assertSame(['a' => $propertyA, 'b' => $propertyB], $metadata->getProperties());
        self::assertSame($propertyA, $metadata->getDefaultProperty());
    }
}
