<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;

/**
 * @internal
 */
final class NullType implements ConstantScalarType
{
    public function describe() : string
    {
        return 'null';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return $value === null;
    }
}
