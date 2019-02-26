<?php

declare(strict_types=1);

namespace Doctrine\Performance\Annotations;

use Doctrine\Tests\Annotations\Fixtures\Controller;
use ReflectionMethod;

trait MethodInitializer
{
    /** @var ReflectionMethod */
    private $method;

    /** @var string */
    private $methodDocBlock;

    /** @var string */
    private $classDocBlock;

    public function initializeMethod() : void
    {
        $this->method         = new ReflectionMethod(Controller::class, 'helloAction');
        $this->methodDocBlock = $this->method->getDocComment();
        $this->classDocBlock  = $this->method->getDeclaringClass()->getDocComment();
    }
}
