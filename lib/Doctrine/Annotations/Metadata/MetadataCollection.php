<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Countable;
use Doctrine\Annotations\Metadata\Exception\MetadataAlreadyExists;
use Doctrine\Annotations\Metadata\Exception\MetadataDoesNotExist;
use IteratorAggregate;
use Traversable;

/**
 * Storage of all annotation metadata. This is a possible extension point i.e. for caching layer.
 */
interface MetadataCollection extends IteratorAggregate, Countable
{
    /**
     * @throws MetadataAlreadyExists
     */
    public function add(AnnotationMetadata ...$metadatas) : void;

    public function include(self $other) : void;

    /**
     * @throws MetadataDoesNotExist
     */
    public function get(string $name) : AnnotationMetadata;

    public function has(string $name) : bool;

    /**
     * @return Traversable<AnnotationMetadata>
     */
    public function getIterator() : Traversable;

    public function count() : int;
}
