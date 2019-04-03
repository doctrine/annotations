<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\Type;
use stdClass;

final class StringTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new StringType();
    }

    public function getDescription() : string
    {
        return 'string';
    }

    /**
     * @return string[][]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [''];
        yield ['123'];
        yield ['hello'];
        yield ['ěščřžýáíé'];
        yield ['😊'];
        yield ["\0"];
    }

    /**
     * @return mixed[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [null];
        yield [false];
        yield [123];
        yield [1.234];
        yield [[123]];
        yield [new stdClass()];
    }
}
