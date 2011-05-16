<?php

namespace Doctrine\Common\Annotations\Annotation;

class ParseAnnotation
{
    public $names;

    public function __construct(array $values)
    {
        if (is_string($values['value'])) {
            $values['value'] = array($values['value']);
        }
        if (!is_array($values['value'])) {
            throw new \RuntimeException(sprintf('@parseAnnotation expects either a string name, or an array of strings, but got %s.', json_encode($values['value'])));
        }

        $this->names = $values['value'];
    }
}