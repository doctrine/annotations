<?php

namespace Doctrine\Common\Annotations;

use ReflectionProperty;

class Psr0PropertyReflection extends ReflectionProperty
{
    public function __construct($psr0Parser, $propertyName)
    {
        $this->psr0Parser = $psr0Parser;
        $this->propertyName = $propertyName;
    }

    public function getName()
    {
        return $this->propertyName;
    }

    public function getDeclaringClass()
    {
        return $this->psr0Parser->getDeclaringPropertyClass($this->propertyName);
    }

    public function getDocComment()
    {
        return $this->psr0Parser->getPropertyDoxygen($this->propertyName);
    }
}
