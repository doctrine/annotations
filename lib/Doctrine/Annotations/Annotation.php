<?php

namespace Doctrine\Annotations;

/**
 * Annotations class.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class Annotation
{
    /**
     * Value property. Common among all derived classes.
     *
     * @var string
     */
    public $value;

    /**
     * Constructor.
     *
     * @param array $data Key-value for properties to be defined in this class.
     */
    public final function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Error handler for unknown property accessor in Annotation class.
     *
     * @param string $name Unknown property name.
     *
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }

    /**
     * Error handler for unknown property mutator in Annotation class.
     *
     * @param string $name  Unknown property name.
     * @param mixed  $value Property value.
     *
     * @throws \BadMethodCallException
     */
    public function __set($name, $value)
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }
}
