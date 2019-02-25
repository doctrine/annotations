<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\Type;
use stdClass;
use function fopen;

final class MixedTypeTest extends TypeTest
{
    protected function createType() : Type
    {
        return new MixedType();
    }

    public function getDescription() : string
    {
        return 'mixed';
    }

    /**
     * @return mixed[]
     */
    public function validValidateValuesProvider() : iterable
    {
        yield [null];
        yield [true];
        yield [123];
        yield [1.234];
        yield ['hello'];
        yield [[123]];
        yield [new stdClass()];
    }

    /**
     * @return resource[][]
     */
    public function invalidValidateValuesProvider() : iterable
    {
        yield [fopen('php://temp', 'r')];
    }
}
