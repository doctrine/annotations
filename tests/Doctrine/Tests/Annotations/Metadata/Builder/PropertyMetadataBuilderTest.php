<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\Builder\PropertyMetadataBuilder;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\StringType;
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
            ->withEnum(['value' => [1, 2, 3], 'literal' => '1, 2, 3'])
            ->build();

        self::assertSame('foo', $metadata->getName());
        self::assertTrue($metadata->isRequired());
        self::assertInstanceOf(StringType::class, $metadata->getType());
        self::assertTrue($metadata->isDefault());
        self::assertSame(['value' => [1, 2, 3], 'literal' => '1, 2, 3'], $metadata->getEnum());
    }
}
