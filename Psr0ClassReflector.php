<?php

namespace Doctrine\Common\Annotations;

class Psr0ClassReflector extends ReflectionClass {
  function __construct($psr0Parser) {
    $this->psr0Parser = $psr0Parser;
  }

  public function getName() {
    return $this->psr0Parser->getClassName();
  }

  public function getDocComment() {
    return $this->psr0Parser->getClassDoxygen();
  }

  public function getNamespaceName() {
    return $this->psr0Parser->getNamespaceName();
  }
}
