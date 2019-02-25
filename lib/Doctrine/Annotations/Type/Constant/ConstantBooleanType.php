<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\ConstantScalarType;

/**
 * @internal
 */
final class ConstantBooleanType extends BooleanType implements ConstantScalarType
{
    /** @var bool */
    private $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue() : bool
    {
        return $this->value;
    }

    public function describe() : string
    {
        if ($this->value === true) {
            return 'true';
        }

        return 'false';
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
