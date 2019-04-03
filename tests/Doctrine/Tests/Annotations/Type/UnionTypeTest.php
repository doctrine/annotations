<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Countable;
use DateTimeImmutable;
use Doctrine\Annotations\Type\Constant\ConstantBooleanType as ConstantbooleanType;
use Doctrine\Annotations\Type\Constant\NullType;
use Doctrine\Annotations\Type\Exception\CompositeTypeRequiresAtLeastTwoSubTypes;
use Doctrine\Annotations\Type\IntegerType;
use Doctrine\Annotations\Type\IntersectionType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Annotations\Type\UnionType;
use IteratorAggregate;
use stdClass;
use Traversable;
use function assert;

final class UnionTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new UnionType(
            new IntegerType(),
            new ObjectType(stdClass::class),
            new ConstantbooleanType(true),
            new IntersectionType(
                new ObjectType(Traversable::class),
                new ObjectType(Countable::class)
            ),
            new NullType()
        );
    }

    public function getDescription() : string
    {
        return 'int|stdClass|true|(Traversable&Countable)|null';
    }

    public function testNotEnoughSubTypes() : void
    {
        $this->expectException(CompositeTypeRequiresAtLeastTwoSubTypes::class);

        new UnionType(new IntegerType());
    }

    public function testGetSubTypes() : void
    {
        $type = $this->getType();
        assert($type instanceof UnionType);

        self::assertCount(5, $type->getSubTypes());
    }

    /**
     * @return (int|object|null)[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [null];
        yield [123];
        yield [true];
        yield [new stdClass()];
        yield [
            new class implements IteratorAggregate,
            Countable {
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
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [false];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new DateTimeImmutable()];
        yield [new class implements IteratorAggregate {
            /**
             * @return Traversable<int>
             */
            public function getIterator() : Traversable
            {
                yield 123;
            }
        }];
        yield [new class implements Countable {
            public function count() : int
            {
                return 0;
            }
        },
        ];
    }
}
