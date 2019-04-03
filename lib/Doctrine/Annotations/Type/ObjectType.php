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
    private $className;

    public function __construct(?string $className)
    {
        $this->className = $className;
    }

    public function describe() : string
    {
        if ($this->className === null) {
            return 'object';
        }

        return $this->className;
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return is_object($value) && ($this->className === null || $value instanceof $this->className);
    }
}
