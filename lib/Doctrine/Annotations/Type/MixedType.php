<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
final class MixedType implements Type
{
    public function describe() : string
    {
        return 'mixed';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return true;
    }
}
