<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\Type;
use stdClass;

final class BooleanTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new BooleanType();
    }

    public function getDescription() : string
    {
        return 'bool';
    }

    /**
     * @return bool[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [true];
        yield [false];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [0];
        yield [0.0];
        yield ['0'];
        yield [[0]];
        yield [new stdClass()];
    }
}
