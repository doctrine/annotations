<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata;

use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Metadata\Exception\InvalidAnnotationTarget;
use const ARRAY_FILTER_USE_KEY;
use function array_filter;
use function implode;

/**
 * Annotation targets represents possible targets at which an annotation could be declared.
 */
final class AnnotationTarget
{
    public const TARGET_CLASS      = 1;
    public const TARGET_METHOD     = 2;
    public const TARGET_PROPERTY   = 4;
    public const TARGET_ANNOTATION = 8;
    public const TARGET_ALL        = self::TARGET_CLASS
        | self::TARGET_METHOD
        | self::TARGET_PROPERTY
        | self::TARGET_ANNOTATION;

    private const LABELS = [
        self::TARGET_CLASS      => 'CLASS',
        self::TARGET_METHOD     => 'METHOD',
        self::TARGET_PROPERTY   => 'PROPERTY',
        self::TARGET_ANNOTATION => 'ANNOTATION',
        self::TARGET_ALL        => 'ALL',
    ];

    /** @var int */
    private $target;

    /**
     * @throws InvalidAnnotationTarget
     */
    public function __construct(int $target)
    {
        if ($target < 0 || $target > self::TARGET_ALL) {
            throw InvalidAnnotationTarget::fromInvalidBitmask($target);
        }

        $this->target = $target;
    }

    public static function class() : self
    {
        return new self(self::TARGET_CLASS);
    }

    public static function method() : self
    {
        return new self(self::TARGET_METHOD);
    }

    public static function property() : self
    {
        return new self(self::TARGET_PROPERTY);
    }

    public static function annotation() : self
    {
        return new self(self::TARGET_ANNOTATION);
    }

    public static function all() : self
    {
        return new self(self::TARGET_ALL);
    }

    public static function fromAnnotation(Target $annotation) : self
    {
        return new self($annotation->targets);
    }

    public function unwrap() : int
    {
        return $this->target;
    }

    public function targetsClass() : bool
    {
        return ($this->target & self::TARGET_CLASS) === self::TARGET_CLASS;
    }

    public function targetsMethod() : bool
    {
        return ($this->target & self::TARGET_METHOD) === self::TARGET_METHOD;
    }

    public function targetsProperty() : bool
    {
        return ($this->target & self::TARGET_PROPERTY) === self::TARGET_PROPERTY;
    }

    public function targetsAnnotation() : bool
    {
        return ($this->target & self::TARGET_ANNOTATION) === self::TARGET_ANNOTATION;
    }

    public function describe() : string
    {
        if ($this->target === self::TARGET_ALL) {
            return self::LABELS[self::TARGET_ALL];
        }

        return implode(
            ', ',
            array_filter(
                self::LABELS,
                function (int $target) : bool {
                    return ($this->target & $target) === $target;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }
}
