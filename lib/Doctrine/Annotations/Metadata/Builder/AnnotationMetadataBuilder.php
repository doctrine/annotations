<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\PropertyMetadata;

/**
 * @internal
 */
final class AnnotationMetadataBuilder
{
    /** @var string */
    private $name;

    /** @var AnnotationTarget */
    private $target;

    /** @var PropertyMetadata[] */
    private $properties = [];

    /** @var bool */
    private $usesConstructor = false;

    public function __construct(string $name)
    {
        $this->name   = $name;
        $this->target = AnnotationTarget::all();
    }

    public function withTarget(AnnotationTarget $target) : self
    {
        $this->target = $target;

        return $this;
    }

    public function withUsingConstructor() : self
    {
        $this->usesConstructor = true;

        return $this;
    }

    public function withProperty(PropertyMetadata $property) : self
    {
        $this->properties[] = $property;

        return $this;
    }

    public function build() : AnnotationMetadata
    {
        return new AnnotationMetadata(
            $this->name,
            $this->target,
            $this->usesConstructor,
            ...$this->properties
        );
    }
}
