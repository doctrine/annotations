<?php

namespace Doctrine\Common\Annotations;

use BadMethodCallException;

use function sprintf;

/**
 * Annotations class.
 */
class Annotation
{
    /**
     * Value property. Common among all derived classes.
     *
     * @var mixed
     */
    public $value;

    /** @param array<string, mixed> $data Key-value for properties to be defined in this class. */
    final public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Error handler for unknown property accessor in Annotation class.
     *
     * @throws BadMethodCallException
     */
    public function __get(string $name)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * Error handler for unknown property mutator in Annotation class.
     *
     * @param mixed $value Property value.
     *
     * @throws BadMethodCallException
     */
    public function __set(string $name, $value)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }
}
