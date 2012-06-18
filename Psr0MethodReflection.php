<?php

namespace Doctrine\Common\Annotations;

use ReflectionMethod;

class Psr0MethodReflection extends ReflectionMethod
{
    public function __construct($psr0Parser, $methodName)
    {
        $this->psr0Parser = $psr0Parser;
        $this->methodName = $methodName;
    }

    public function getName()
    {
        return $this->methodName;
    }

    public function getDeclaringClass()
    {
        return $this->psr0Parser->getDeclaringMethodClass($this->methodName);
    }

    public function getDocComment()
    {
        return $this->psr0Parser->getMethodDoxygen($this->methodName);
    }
}
