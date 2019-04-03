<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Annotation\Attribute;
use Doctrine\Annotations\Annotation\Attributes;
use Doctrine\Annotations\Annotation\Enum;
use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Type\ArrayType;
use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\StringType;

/**
 * Internal meta-annotations exposed by the Annotations library to declare custom user-land annotations.
 *
 * @internal
 */
final class InternalAnnotations
{
    public static function createMetadata() : MetadataCollection
    {
        return new TransientMetadataCollection(
            new AnnotationMetadata(
                Attribute::class,
                AnnotationTarget::annotation(),
                false,
                new PropertyMetadata(
                    'name',
                    new StringType(),
                    true,
                    true
                ),
                new PropertyMetadata(
                    'type',
                    new StringType(),
                    true
                ),
                new PropertyMetadata(
                    'required',
                    new BooleanType()
                )
            ),
            new AnnotationMetadata(
                Attributes::class,
                AnnotationTarget::class(),
                false,
                new PropertyMetadata(
                    'value',
                    new ArrayType(new MixedType(), new ObjectType(Attribute::class)),
                    true,
                    true
                )
            ),
            new AnnotationMetadata(
                Enum::class,
                AnnotationTarget::property(),
                true,
                new PropertyMetadata(
                    'value',
                    new ArrayType(new MixedType(), new MixedType()),
                    true,
                    true
                ),
                new PropertyMetadata(
                    'literal',
                    new ArrayType(new MixedType(), new MixedType())
                )
            ),
            new AnnotationMetadata(
                Target::class,
                AnnotationTarget::class(),
                true,
                new PropertyMetadata(
                    'value',
                    new ArrayType(new MixedType(), new StringType()),
                    false,
                    true
                )
            )
        );
    }
}
