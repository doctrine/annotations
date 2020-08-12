<?php

namespace Doctrine\Common\Annotations\Annotation;

use RuntimeException;

use function is_array;
use function is_string;
use function json_encode;
use function sprintf;

/**
 * Annotation that can be used to signal to the parser to ignore specific
 * annotations during the parsing process.
 *
 * @Annotation
 */
final class IgnoreAnnotation
{
    /** @var array */
    public $names;

    /**
     * @param array $values
     *
     * @throws RuntimeException
     */
    public function __construct(array $values)
    {
        if (is_string($values['value'])) {
            $values['value'] = [$values['value']];
        }

        if (! is_array($values['value'])) {
            throw new RuntimeException(sprintf('@IgnoreAnnotation expects either a string name, or an array of strings, but got %s.', json_encode($values['value'])));
        }

        $this->names = $values['value'];
    }
}
