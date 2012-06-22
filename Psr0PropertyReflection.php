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
    protected function getPsr0Parser()
    {
        return $this->psr0Parser->getPsr0ParserFor('property', $this->propertyName);
    }
    public function getDeclaringClass()
    {
        return $this->getPsr0Parser()->getClassReflection();
    }
    public function getNamespaceName()
    {
        return $this->getPsr0Parser()->getNamespaceName();
    }
    public function getDocComment()
    {
        return $this->getPsr0Parser()->getDoxygen('property', $this->propertyName);
    }
    public function getUseStatements()
    {
        return $this->getPsr0Parser()->getUseStatements();
    }
}
