<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
interface Type
{
    public function describe() : string;

    /**
     * @param mixed $value
     */
    public function validate($value) : bool;
}
