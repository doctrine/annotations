<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use ArrayIterator;
use DateTimeImmutable;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\Type;
use IteratorAggregate;
use Traversable;

final class ObjectTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new ObjectType(Traversable::class);
    }

    public function getDescription() : string
    {
        return 'Traversable';
    }

    /**
     * @return object[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [new ArrayIterator([])];
        yield [(static function () {
            yield from [];
        })(),
        ];
        yield [new class implements IteratorAggregate {
                /**
                 * @return int[]
                 */
            public function getIterator() : iterable
            {
                yield 1;
            }
        },
        ];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [true];
        yield [0];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new DateTimeImmutable()];
    }
}
