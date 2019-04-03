<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\Builder\PropertyMetadataBuilder;
use Doctrine\Annotations\Type\Constant\ConstantIntegerType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\UnionType;
use PHPUnit\Framework\TestCase;

final class PropertyMetadataBuilderTest extends TestCase
{
    public function testDefaults() : void
    {
        $metadata = (new PropertyMetadataBuilder('foo'))->build();

        self::assertSame('foo', $metadata->getName());
        self::assertFalse($metadata->isRequired());
        self::assertInstanceOf(MixedType::class, $metadata->getType());
        self::assertFalse($metadata->isDefault());
        self::assertNull($metadata->getEnum());
    }

    public function testBuilding() : void
    {
        $metadata = (new PropertyMetadataBuilder('foo'))
            ->withBeingDefault()
            ->withBeingRequired()
            ->withType(new StringType())
            ->withEnum(new UnionType(new ConstantIntegerType(1), new ConstantIntegerType(2)))
            ->build();

        self::assertSame('foo', $metadata->getName());
        self::assertTrue($metadata->isRequired());
        self::assertInstanceOf(StringType::class, $metadata->getType());
        self::assertTrue($metadata->isDefault());
        self::assertSame('1|2', $metadata->getEnum()->describe());
    }
}
