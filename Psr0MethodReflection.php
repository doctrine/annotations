<?php

namespace Doctrine\Common\Annotations;

use ReflectionMethod;

class Psr0MethodReflection extends ReflectionMethod
{
    /**
     * The PSR-0 parser object.
     *
     * @var Psr0Parser
     */
    protected $psr0Parser;

    /**
     * The name of the method.
     *
     * @var string
     */
    protected $methodName;

    public function __construct($psr0Parser, $methodName)
    {
        $this->psr0Parser = $psr0Parser;
        $this->methodName = $methodName;
    }
    public function getName()
    {
        return $this->methodName;
    }
    protected function getPsr0Parser()
    {
        return $this->psr0Parser->getPsr0ParserFor('method', $this->methodName);
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
        return $this->getPsr0Parser()->getDoxygen('method', $this->methodName);
    }
    public function getUseStatements()
    {
        return $this->getPsr0Parser()->getUseStatements();
    }
}
