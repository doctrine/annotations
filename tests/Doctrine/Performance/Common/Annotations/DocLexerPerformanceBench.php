<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\DocLexer;

/**
 * @BeforeMethods({"initializeMethod", "initialize"})
 */
final class DocLexerPerformanceBench
{
    use MethodInitializer;

    /** @var DocLexer */
    private $lexer;

    public function initialize() : void
    {
        $this->lexer = new DocLexer();
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     */
    public function benchMethod() : void
    {
        $this->lexer->setInput($this->methodDocBlock);
    }

    /**
     * @Revs(500)
     * @Iterations(5)
     */
    public function benchClass() : void
    {
        $this->lexer->setInput($this->classDocBlock);
    }
}
