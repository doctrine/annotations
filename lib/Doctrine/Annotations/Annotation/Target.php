<?php

namespace Doctrine\Annotations\Annotation;

use Doctrine\Annotations\Metadata\AnnotationTarget;

/**
 * Annotation that can be used to signal to the parser
 * to check the annotation target during the parsing process.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 *
 * @Annotation
 */
final class Target
{
    public const TARGET_CLASS      = AnnotationTarget::TARGET_CLASS;
    public const TARGET_METHOD     = AnnotationTarget::TARGET_METHOD;
    public const TARGET_PROPERTY   = AnnotationTarget::TARGET_PROPERTY;
    public const TARGET_ANNOTATION = AnnotationTarget::TARGET_ANNOTATION;
    public const TARGET_ALL        = AnnotationTarget::TARGET_ALL;

    /**
     * @var array
     */
    private static $map = [
        'ALL'        => self::TARGET_ALL,
        'CLASS'      => self::TARGET_CLASS,
        'METHOD'     => self::TARGET_METHOD,
        'PROPERTY'   => self::TARGET_PROPERTY,
        'ANNOTATION' => self::TARGET_ANNOTATION,
    ];

    /**
     * @var array
     */
    public $value;

    /**
     * Targets as bitmask.
     *
     * @var integer
     */
    public $targets;

    /**
     * Literal target declaration.
     *
     * @var string
     */
    public $literal;

    /**
     * Annotation constructor.
     *
     * @param array $values
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        if (!isset($values['value'])){
            $values['value'] = null;
        }
        if (is_string($values['value'])){
            $values['value'] = [$values['value']];
        }
        if (!is_array($values['value'])){
            throw new \InvalidArgumentException(
                sprintf('@Target expects either a string value, or an array of strings, "%s" given.',
                    is_object($values['value']) ? get_class($values['value']) : gettype($values['value'])
                )
            );
        }

        $bitmask = 0;
        foreach ($values['value'] as $literal) {
            if(!isset(self::$map[$literal])){
                throw new \InvalidArgumentException(
                    sprintf('Invalid Target "%s". Available targets: [%s]',
                            $literal,  implode(', ', array_keys(self::$map)))
                );
            }
            $bitmask |= self::$map[$literal];
        }

        $this->targets  = $bitmask;
        $this->value    = $values['value'];
        $this->literal  = implode(', ', $this->value);
    }
}
