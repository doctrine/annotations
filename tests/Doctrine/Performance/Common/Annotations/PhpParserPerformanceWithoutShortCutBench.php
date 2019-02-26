<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\PhpParser;
use ReflectionClass;
use SingleClassLOC1000;

/**
 * @BeforeMethods({"initialize"})
 */
final class PhpParserPerformanceWithoutShortCutBench
{
    /** @var ReflectionClass */
    private $class;

    /** @var PhpParser */
    private $parser;

    public function initialize() : void
    {
        $this->class  = new ReflectionClass(SingleClassLOC1000::class);
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
