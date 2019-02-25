<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

/**
 * @internal
 */
interface CompositeType extends Type
{
    /**
     * @return Type[]
     */
    public function getSubTypes() : array;
}
