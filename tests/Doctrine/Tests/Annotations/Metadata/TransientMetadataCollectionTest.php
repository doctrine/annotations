<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\Exception\MetadataAlreadyExists;
use Doctrine\Annotations\Metadata\Exception\MetadataDoesNotExist;
use Doctrine\Annotations\Metadata\TransientMetadataCollection;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

final class TransientMetadataCollectionTest extends TestCase
{
    public function testCollectionInterface() : void
    {
        $foo = $this->createDummyMetadata('Foo');
        $bar = $this->createDummyMetadata('Bar');
        $baz = $this->createDummyMetadata('Baz');

        $collection = new TransientMetadataCollection();

        self::assertCount(0, $collection);
        self::assertFalse($collection->has('Foo'));
        self::assertFalse($collection->has('Bar'));
        self::assertFalse($collection->has('Baz'));
        self::assertSame([], iterator_to_array($collection));

        $collection->add($foo, $bar);

        self::assertCount(2, $collection);
        self::assertTrue($collection->has('Foo'));
        self::assertSame($foo, $collection->get('Foo'));
        self::assertTrue($collection->has('Bar'));
        self::assertSame($bar, $collection->get('Bar'));
        self::assertSame([$foo, $bar], iterator_to_array($collection));

        $collection->add($baz);

        self::assertCount(3, $collection);
        self::assertTrue($collection->has('Baz'));
        self::assertSame($baz, $collection->get('Baz'));
        self::assertSame([$foo, $bar, $baz], iterator_to_array($collection));
    }

    public function testInclude() : void
    {
        $foo = $this->createDummyMetadata('Foo');
        $bar = $this->createDummyMetadata('Bar');
        $baz = $this->createDummyMetadata('Baz');

        $collection = new TransientMetadataCollection($foo);
        $collection->include(new TransientMetadataCollection($bar, $baz));

        self::assertCount(3, $collection);
        self::assertSame([$foo, $bar, $baz], iterator_to_array($collection));
    }

    public function testMetadataInConstructor() : void
    {
        self::assertCount(
            2,
            new TransientMetadataCollection(
                $this->createDummyMetadata('A'),
                $this->createDummyMetadata('B')
            )
        );
    }

    public function testDuplicateMetadata() : void
    {
        $this->expectException(MetadataAlreadyExists::class);
        $this->expectExceptionMessage('Metadata for annotation "Foo" already exists.');

        new TransientMetadataCollection(
            $this->createDummyMetadata('Foo'),
            $this->createDummyMetadata('Bar'),
            $this->createDummyMetadata('Foo')
        );
    }

    public function testGettingNonexistentMetadata() : void
    {
        $collection = new TransientMetadataCollection();

        $this->expectException(MetadataDoesNotExist::class);
        $this->expectExceptionMessage('Metadata for annotation "Foo" does not exist.');

        $collection->get('Foo');
    }

    private function createDummyMetadata(string $name) : AnnotationMetadata
    {
        return new AnnotationMetadata($name, AnnotationTarget::all(), false);
    }
}
