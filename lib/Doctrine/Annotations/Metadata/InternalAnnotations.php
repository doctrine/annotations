<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Annotation\Attribute;
use Doctrine\Annotations\Annotation\Attributes;
use Doctrine\Annotations\Annotation\Enum;
use Doctrine\Annotations\Annotation\Target;

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
                    ['type' => 'string'],
                    true,
                    true
                ),
                new PropertyMetadata(
                    'type',
                    ['type' => 'string'],
                    true
                ),
                new PropertyMetadata(
                    'required',
                    ['type' => 'boolean']
                )
            ),
            new AnnotationMetadata(
                Attributes::class,
                AnnotationTarget::class(),
                false,
                new PropertyMetadata(
                    'value',
                    [
                        'type'       => 'array',
                        'array_type' =>Attribute::class,
                        'value'      =>'array<' . Attribute::class . '>',
                    ],
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
                    ['type' => 'array'],
                    true,
                    true
                ),
                new PropertyMetadata(
                    'literal',
                    ['type' => 'array']
                )
            ),
            new AnnotationMetadata(
                Target::class,
                AnnotationTarget::class(),
                true,
                new PropertyMetadata(
                    'value',
                    [
                        'type'      =>'array',
                        'array_type'=>'string',
                        'value'     =>'array<string>',
                    ],
                    false,
                    true
                )
            )
        );
    }
}
