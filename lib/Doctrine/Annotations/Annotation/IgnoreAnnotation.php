<?php

namespace Doctrine\Annotations\Annotation;

/**
 * Annotation that can be used to signal to the parser to ignore specific
 * annotations during the parsing process.
 *
 * @Annotation
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class IgnoreAnnotation
{
    /**
     * @var array
     */
    public $names;

    /**
     * Constructor.
     *
     * @param array $values
     *
     * @throws \RuntimeException
     */
    public function __construct(array $values)
    {
        if (is_string($values['value'])) {
            $values['value'] = [$values['value']];
        }
        if (!is_array($values['value'])) {
            throw new \RuntimeException(sprintf('@IgnoreAnnotation expects either a string name, or an array of strings, but got %s.', json_encode($values['value'])));
        }

        $this->names = $values['value'];
    }
}
