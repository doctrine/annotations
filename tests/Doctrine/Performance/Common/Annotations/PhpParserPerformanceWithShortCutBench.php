<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Tests\Common\Annotations\Fixtures\NamespacedSingleClassLOC1000;
use ReflectionClass;

/**
 * @BeforeMethods({"initialize"})
 */
final class PhpParserPerformanceWithShortCutBench
{
    /** @var ReflectionClass */
    private $class;

    /** @var PhpParser */
    private $parser;

    public function initialize() : void
    {
        $this->class  = new ReflectionClass(NamespacedSingleClassLOC1000::class);
        $this->parser = new PhpParser();
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     */
    public function bench() : void
    {
        $this->parser->parseClass($this->class);
    }
}
