<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use ArrayObject;
use Countable;
use Doctrine\Annotations\Type\ArrayType;
use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\IntegerType;
use Doctrine\Annotations\Type\IntersectionType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\UnionType;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

final class ArrayTypeTest extends TestCase
{
    /**
     * @dataProvider descriptionProvider()
     */
    public function testDescription(ArrayType $type, string $description) : void
    {
        self::assertSame($description, $type->describe());
    }

    /**
     * @param mixed[] $value
     *
     * @dataProvider validValuesProvider()
     */
    public function testValidValues(ArrayType $type, array $value) : void
    {
        self::assertTrue($type->validate($value));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider invalidValuesProvider()
     */
    public function testInvalidValues(ArrayType $type, $value) : void
    {
        self::assertFalse($type->validate($value));
    }

    public function testGetKeyType() : void
    {
        $type = new ArrayType(new IntegerType(), new StringType());

        self::assertInstanceOf(IntegerType::class, $type->getKeyType());
    }

    public function testGetValueType() : void
    {
        $type = new ArrayType(new IntegerType(), new StringType());

        self::assertInstanceOf(StringType::class, $type->getValueType());
    }

    /**
     * @return (ArrayType|string)[]
     */
    public function descriptionProvider() : iterable
    {
        yield [
            new ArrayType(new MixedType(), new MixedType()),
            'array<mixed>',
        ];
        yield [
            new ArrayType(new MixedType(), new IntegerType()),
            'array<int>',
        ];
        yield [
            new ArrayType(new UnionType(new IntegerType(), new StringType()), new IntegerType()),
            'array<int>',
        ];
        yield [
            new ArrayType(new UnionType(new StringType(), new IntegerType()), new IntegerType()),
            'array<int>',
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            'array<int, int>',
        ];
        yield [
            new ArrayType(new IntegerType(), new UnionType(new IntegerType(), new BooleanType())),
            'array<int, int|bool>',
        ];
        yield [
            new ArrayType(
                new UnionType(new IntegerType(), new ObjectType(null)),
                new UnionType(
                    new StringType(),
                    new IntersectionType(new ObjectType(Traversable::class), new ObjectType(Countable::class))
                )
            ),
            'array<int|object, string|(Traversable&Countable)>',
        ];
    }

    /**
     * @return (ArrayType|mixed[])[]
     */
    public function validValuesProvider() : iterable
    {
        yield [
            new ArrayType(new MixedType(), new MixedType()),
            [],
        ];
        yield [
            new ArrayType(new MixedType(), new MixedType()),
            [1 => 2],
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            [],
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            [
                1 => 2,
                3 => 4,
            ],
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            [1, 2, 3],
        ];
        yield [
            new ArrayType(
                new UnionType(new IntegerType(), new StringType()),
                new UnionType(new StringType(), new BooleanType())
            ),
            [
                1     => false,
                'foo' => true,
                3     => 'bar',
                'baz' => 'bah',
            ],
        ];
        yield [
            new ArrayType(
                new UnionType(new StringType(), new BooleanType()),
                new UnionType(
                    new IntegerType(),
                    new IntersectionType(new ObjectType(Traversable::class), new ObjectType(Countable::class))
                )
            ),
            [
                'foo' => 123,
                'bar' => new class implements IteratorAggregate, Countable {
                    /**
                     * @return Traversable<int>
                     */
                    public function getIterator() : Traversable
                    {
                        yield 123;
                    }

                    public function count() : int
                    {
                        return 1;
                    }
                },
            ],
        ];
    }

    /**
     * @return (ArrayType|mixed[])[]
     */
    public function invalidvaluesProvider() : iterable
    {
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            null,
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            123,
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            new ArrayObject(),
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            ['foo'],
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            ['foo' => 123],
        ];
        yield [
            new ArrayType(new IntegerType(), new IntegerType()),
            [
                1 => 2,
                3 => 'foo',
                4 => 5,
            ],
        ];
        yield [
            new ArrayType(
                new UnionType(new StringType(), new BooleanType()),
                new UnionType(
                    new IntegerType(),
                    new IntersectionType(new ObjectType(Traversable::class), new ObjectType(Countable::class))
                )
            ),
            [
                'foo' => new stdClass(),
                123 => 'bar',
            ],
        ];
    }
}
