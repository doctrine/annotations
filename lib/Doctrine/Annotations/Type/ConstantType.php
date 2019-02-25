<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
interface ConstantType extends Type
{
    /**
     * @return mixed
     */
    public function getValue();
}
