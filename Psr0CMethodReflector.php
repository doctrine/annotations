<?php

namespace Doctrine\Common\Annotations;

class Psr0MethodReflector extends ReflectionClass {
  function __construct($psr0Parser, $methodName) {
    $this->psr0Parser = $psr0Parser;
    $this->methodName = $methodName;
  }

  function getName() {
    return $this->methodName;
  }

  function getDeclaringClass() {
    return $this->psr0Parser->getDeclaringMethodClass($this->methodName);
  }

  function getDocComment() {
    return $this->psr0Parser->getMethodDoxygen($this->methodName);
  }
}
