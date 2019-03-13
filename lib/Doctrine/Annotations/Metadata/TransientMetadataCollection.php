<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Metadata\Exception\MetadataAlreadyExists;
use Doctrine\Annotations\Metadata\Exception\MetadataDoesNotExist;
use Traversable;
use function array_key_exists;
use function array_values;
use function count;

/**
 * In-memory metadata collection.
 *
 * @internal
 */
final class TransientMetadataCollection implements MetadataCollection
{
    /** @var array<string, AnnotationMetadata> */
    private $metadata = [];

    public function __construct(AnnotationMetadata ...$metadatas)
    {
        $this->add(...$metadatas);
    }

    /**
     * @throws MetadataAlreadyExists
     */
    public function add(AnnotationMetadata ...$metadatas) : void
    {
        foreach ($metadatas as $metadata) {
            if (isset($this->metadata[$metadata->getName()])) {
                throw MetadataAlreadyExists::new($metadata);
            }

            $this->metadata[$metadata->getName()] = $metadata;
        }
    }

    public function include(MetadataCollection $other) : void
    {
        $this->add(...$other);
    }

    /**
     * @throws MetadataDoesNotExist
     */
    public function get(string $name) : AnnotationMetadata
    {
        if (! isset($this->metadata[$name])) {
            throw MetadataDoesNotExist::new($name);
        }

        return $this->metadata[$name];
    }

    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->metadata);
    }

    /**
     * @return Traversable<AnnotationMetadata>
     */
    public function getIterator() : Traversable
    {
        yield from array_values($this->metadata);
    }

    public function count() : int
    {
        return count($this->metadata);
    }
}
