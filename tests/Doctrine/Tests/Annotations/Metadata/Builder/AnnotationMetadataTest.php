<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Metadata\Builder;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\Exception\TooManyDefaultProperties;
use Doctrine\Annotations\Metadata\PropertyMetadata;
use PHPUnit\Framework\TestCase;

final class AnnotationMetadataTest extends TestCase
{
    public function testMultipleDefaultProperties() : void
    {
        $this->expectException(TooManyDefaultProperties::class);
        $this->expectExceptionMessage(
            'The annotation "Foo" can only have at most one default property, currently has 2: "a", "b".'
        );

        new AnnotationMetadata(
            'Foo',
            AnnotationTarget::all(),
            false,
            new PropertyMetadata(
                'a',
                ['type' => 'string'],
                true,
                true
            ),
            new PropertyMetadata(
                'b',
                ['type' => 'string'],
                true,
                true
            )
        );
    }
}
