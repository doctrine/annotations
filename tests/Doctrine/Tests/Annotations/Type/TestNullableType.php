<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\Constant\NullType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Annotations\Type\UnionType;

final class TestNullableType
{
    public static function fromType(Type $type) : UnionType
    {
        return new UnionType($type, new NullType());
    }
}
