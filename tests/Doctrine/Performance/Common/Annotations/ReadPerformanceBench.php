<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @BeforeMethods({"initializeMethod", "initialize"})
 */
final class ReadPerformanceBench
{
    use MethodInitializer;

    /** @var AnnotationReader */
    private $reader;

    public function initialize() : void
    {
        $this->reader = new AnnotationReader();
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
