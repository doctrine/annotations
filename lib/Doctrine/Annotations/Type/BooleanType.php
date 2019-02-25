<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_bool;

/**
 * @internal
 */
class BooleanType implements ScalarType
{
    public function describe() : string
    {
        return 'bool';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_bool($value);
    }
}
