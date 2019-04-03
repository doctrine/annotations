<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Constant;

use Doctrine\Annotations\Type\ConstantScalarType;
use Doctrine\Annotations\Type\StringType as GenericStringType;
use function addcslashes;
use function sprintf;

/**
 * @internal
 */
final class ConstantStringType extends GenericStringType implements ConstantScalarType
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function describe() : string
    {
        return sprintf('"%s"', addcslashes($this->value, '\"'));
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        return $value === $this->value;
    }
}
