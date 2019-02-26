<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Tests\Common\Annotations\Fixtures\Controller;
use ReflectionMethod;

/**
 * @BeforeMethods({"initialize"})
 */
final class CachedReadPerformanceWithInMemoryBench
{
    /** @var CachedReader */
    private $reader;

    /** @var ReflectionMethod */
    private $method;

    public function initialize() : void
    {
        $this->reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $this->method = new ReflectionMethod(Controller::class, 'helloAction');
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     */
    public function bench() : void
    {
        $this->reader->getMethodAnnotations($this->method);
    }
}
