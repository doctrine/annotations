<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\FloatType;
use Doctrine\Annotations\Type\Type;
use stdClass;

final class FloatTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new FloatType();
    }

    public function getDescription() : string
    {
        return 'float';
    }

    /**
     * @return float[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [0.0];
        yield [123e-456];
        yield [1.234];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [123];
        yield ['0.0'];
        yield [[0.0]];
        yield [new stdClass()];
    }
}
