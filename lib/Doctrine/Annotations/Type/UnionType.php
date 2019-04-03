<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use Doctrine\Annotations\Type\Exception\CompositeTypeRequiresAtLeastTwoSubTypes;
use function count;

/**
 * @internal
 */
final class UnionType implements CompositeType
{
    /** @var Type[] */
    private $subTypes;

    public function __construct(Type ...$subTypes)
    {
        if (count($subTypes) < 2) {
            throw CompositeTypeRequiresAtLeastTwoSubTypes::fromInsufficientAmount(count($subTypes));
        }

        $this->subTypes = $subTypes;
    }

    /**
     * @return Type[]
     */
    public function getSubTypes() : array
    {
        return $this->subTypes;
    }

    public function describe() : string
    {
        return CompositeTypeDescriber::describe('|', $this->subTypes);
    }

    /**
     * @param mixed $value
     */
    public function validate($value) : bool
    {
        foreach ($this->subTypes as $subType) {
            if (! $subType->validate($value)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
