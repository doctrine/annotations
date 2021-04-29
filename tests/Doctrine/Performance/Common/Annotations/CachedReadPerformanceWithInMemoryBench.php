<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Tests\Common\Annotations\Fixtures\Controller;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @BeforeMethods({"initialize"})
 */
final class CachedReadPerformanceWithInMemoryBench
{
    /** @var PsrCachedReader */
    private $reader;

    /** @var ReflectionMethod */
    private $method;

    public function initialize(): void
    {
        $this->reader = new PsrCachedReader(new AnnotationReader(), new ArrayAdapter());
        $this->method = new ReflectionMethod(Controller::class, 'helloAction');
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     */
    public function bench(): void
    {
        $this->reader->getMethodAnnotations($this->method);
    }
}
