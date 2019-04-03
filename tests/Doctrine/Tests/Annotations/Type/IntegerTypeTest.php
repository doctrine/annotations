<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\IntegerType;
use Doctrine\Annotations\Type\Type;
use stdClass;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class IntegerTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new IntegerType();
    }

    public function getDescription() : string
    {
        return 'int';
    }

    /**
     * @return int[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [0];
        yield [123];
        yield [PHP_INT_MIN];
        yield [PHP_INT_MAX];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [1.234];
        yield ['0'];
        yield [[0]];
        yield [new stdClass()];
    }
}
