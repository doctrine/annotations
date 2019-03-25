<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\PropertyMetadata;

/**
 * @internal
 */
final class PropertyMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var string[]|null */
    private $type;

    /** @var bool */
    private $required = false;

    /** @var bool */
    private $default = false;

    /** @var array<string, array|int|float|string|bool>|null */
    private $enum;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string[] $type
     */
    public function withType(array $type) : self
    {
        $this->type = $type;

        return $this;
    }

    public function withBeingRequired() : self
    {
        $this->required = true;

        return $this;
    }

    public function withBeingDefault() : self
    {
        $this->default = true;

        return $this;
    }

    /**
     * @param array<string, array|int|float|string|bool> $enum
     */
    public function withEnum(array $enum) : self
    {
        $this->enum = $enum;

        return $this;
    }

    public function build() : PropertyMetadata
    {
        return new PropertyMetadata(
            $this->name,
            $this->type,
            $this->required,
            $this->default,
            $this->enum
        );
    }
}
