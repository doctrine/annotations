<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\Builder\PropertyMetadataBuilder;
use PHPUnit\Framework\TestCase;

final class PropertyMetadataBuilderTest extends TestCase
{
    public function testDefaults() : void
    {
        $metadata = (new PropertyMetadataBuilder('foo'))->build();

        self::assertSame('foo', $metadata->getName());
        self::assertFalse($metadata->isRequired());
        self::assertNull($metadata->getType());
        self::assertFalse($metadata->isDefault());
        self::assertNull($metadata->getEnum());
    }

    public function testBuilding() : void
    {
        $metadata = (new PropertyMetadataBuilder('foo'))
            ->withBeingDefault()
            ->withBeingRequired()
            ->withType(['type' => 'string'])
            ->withEnum(['value' => [1, 2, 3], 'literal' => '1, 2, 3'])
            ->build();

        self::assertSame('foo', $metadata->getName());
        self::assertTrue($metadata->isRequired());
        self::assertSame(['type' => 'string'], $metadata->getType());
        self::assertTrue($metadata->isDefault());
        self::assertSame(['value' => [1, 2, 3], 'literal' => '1, 2, 3'], $metadata->getEnum());
    }
}
