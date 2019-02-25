<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
interface ConstantScalarType extends ScalarType, ConstantType
{
    /**
     * @return bool|int|float|string|null
     */
    public function getValue();
}
