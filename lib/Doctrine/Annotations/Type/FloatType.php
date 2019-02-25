<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_float;

/**
 * @internal
 */
class FloatType implements ScalarType
{
    public function describe() : string
    {
        return 'float';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_float($value);
    }
}
