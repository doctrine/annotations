<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

/**
 * Property metadata represents information about the definition of a single property of an annotation, it's name,
 * accepted types, whether it's required and whether it's default.
 */
final class PropertyMetadata
{
    /** @var string */
    private $name;

    /** @var array<string, string>|null */
    private $type;

    /** @var bool */
    private $required;

    /** @var bool */
    private $default;

    /** @var array<int|float|string|bool>|null */
    private $enum;

    /**
     * @param array<string, string>             $type
     * @param array<int|float|string|bool>|null $enum
     */
    public function __construct(
        string $name,
        ?array $type,
        bool $required = false,
        bool $default = false,
        ?array $enum = null
    ) {
        $this->name     = $name;
        $this->type     = $type;
        $this->required = $required;
        $this->default  = $default;
        $this->enum     = $enum;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * @return array<string, string>|null
     */
    public function getType() : ?array
    {
        return $this->type;
    }

    public function isDefault() : bool
    {
        return $this->default;
    }

    /**
     * @return array<string, array|int|float|string|bool>|null
     */
    public function getEnum() : ?array
    {
        return $this->enum;
    }
}
