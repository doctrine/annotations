<?php

declare(strict_types=1);

namespace Doctrine\Performance\Common\Annotations;

use Doctrine\Common\Annotations\DocParser;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;

/**
 * @BeforeMethods({"initializeMethod", "initialize"})
 */
final class DocParserPerformanceBench
{
    use MethodInitializer;

    private const IMPORTS = [
        'ignorephpdoc'     => 'Annotations\Annotation\IgnorePhpDoc',
        'ignoreannotation' => 'Annotations\Annotation\IgnoreAnnotation',
        'route'            => Route::class,
        'template'         => Template::class,
        '__NAMESPACE__'    => 'Doctrine\Tests\Common\Annotations\Fixtures',
    ];

    private const IGNORED = [
        'access', 'author', 'copyright', 'deprecated', 'example', 'ignore',
        'internal', 'link', 'see', 'since', 'tutorial', 'version', 'package',
        'subpackage', 'name', 'global', 'param', 'return', 'staticvar',
        'static', 'var', 'throws', 'inheritdoc',
    ];

    /** @var DocParser */
    private $parser;

    public function initialize() : void
    {
        $this->parser = new DocParser();

        $this->parser->setImports(self::IMPORTS);
        $this->parser->setIgnoredAnnotationNames(array_fill_keys(self::IGNORED, true));
        $this->parser->setIgnoreNotImportedAnnotations(true);
    }

    /**
     * @Revs(200)
     * @Iterations(5)
     */
    public function benchMethodParsing() : void
    {
        $this->parser->parse($this->methodDocBlock);
    }

    /**
     * @Revs(200)
     * @Iterations(5)
     */
    public function benchClassParsing() : void
    {
        $this->parser->parse($this->classDocBlock);
    }
}
