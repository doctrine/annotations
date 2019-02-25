<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_object;

/**
 * @internal
 */
class ObjectType implements Type
{
    /** @var string|null */
    private $name;

    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function describe() : string
    {
        if ($this->name === null) {
            return 'object';
        }

        return $this->name;
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_object($value) && ($this->name === null || $value instanceof $this->name);
    }
}
