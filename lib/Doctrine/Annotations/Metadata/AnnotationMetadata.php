<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Metadata\Exception\TooManyDefaultProperties;
use function array_combine;
use function array_filter;
use function array_map;
use function array_values;
use function count;

/**
 * Annotation metadata represents information about the definition a single annotation - its name, allowed targets,
 * construction strategy and properties.
 */
final class AnnotationMetadata
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var bool */
    private $usesConstructor;

    /** @var array<string, PropertyMetadata> */
    private $properties;

    /** @var PropertyMetadata|null */
    private $defaultProperty;

    public function __construct(
        string $name,
        AnnotationTarget $target,
        bool $hasConstructor,
        PropertyMetadata ...$properties
    ) {
        $this->name            = $name;
        $this->target          = $target;
        $this->usesConstructor = $hasConstructor;
        $this->properties      = array_combine(
            array_map(
                static function (PropertyMetadata $property) : string {
                    return $property->getName();
                },
                $properties
            ),
            $properties
        );

        $defaultProperties = array_filter(
            $properties,
            static function (PropertyMetadata $property) : bool {
                return $property->isDefault();
            }
        );

        if (count($defaultProperties) > 1) {
            throw TooManyDefaultProperties::new($name, ...$defaultProperties);
        }

        $this->defaultProperty = array_values($defaultProperties)[0] ?? null;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getTarget() : AnnotationTarget
    {
        return $this->target;
    }

    public function usesConstructor() : bool
    {
        return $this->usesConstructor;
    }

    /**
     * @return array<string, PropertyMetadata>
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getDefaultProperty() : ?PropertyMetadata
    {
        return $this->defaultProperty;
    }
}
