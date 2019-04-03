<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function is_array;
use function sprintf;

/**
 * @internal
 */
class ArrayType implements Type
{
    /** @var Type */
    private $keyType;

    /** @var Type */
    private $valueType;

    public function __construct(Type $keyType, Type $valueType)
    {
        $this->keyType   = $keyType;
        $this->valueType = $valueType;
    }

    public function getKeyType() : Type
    {
        return $this->keyType;
    }

    public function getValueType() : Type
    {
        return $this->valueType;
    }

    public function describe() : string
    {
        if ($this->keyType instanceof MixedType
            || $this->keyType->describe() === 'int|string'
            || $this->keyType->describe() === 'string|int'
        ) {
            return sprintf('array<%s>', $this->valueType->describe());
        }

        return sprintf('array<%s, %s>', $this->keyType->describe(), $this->valueType->describe());
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $innerValue) {
            if (! $this->keyType->validate($key)) {
                return false;
            }

            if (! $this->valueType->validate($innerValue)) {
                return false;
            }
        }

        return true;
    }
}
