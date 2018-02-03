<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\DocLexer;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures/Annotation/Route.php';
require_once __DIR__ . '/Fixtures/Annotation/Template.php';
require_once __DIR__ . '/Fixtures/Annotation/Secure.php';
require_once __DIR__ . '/Fixtures/SingleClassLOC1000.php';

class PerformanceTest extends TestCase
{
    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithInMemory()
    {
        $reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $method = $this->getMethod();

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader (in-memory)', $time, $c);
    }

    /**
     * @group performance
     */
    public function testCachedReadPerformanceWithFileCache()
    {
        $method = $this->getMethod();

        // prime cache
        $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
        $reader->getMethodAnnotations($method);

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $reader = new FileCacheReader(new AnnotationReader(), sys_get_temp_dir());
            $reader->getMethodAnnotations($method);
            clearstatcache();
        }
        $time = microtime(true) - $time;

        $this->printResults('cached reader (file)', $time, $c);
    }

    /**
     * @group performance
     */
    public function testReadPerformance()
    {
        $method = $this->getMethod();

        $time = microtime(true);
        for ($i=0,$c=150; $i<$c; $i++) {
            $reader = new AnnotationReader();
            $reader->getMethodAnnotations($method);
        }
        $time = microtime(true) - $time;

        $this->printResults('reader', $time, $c);
    }

    /**
     * @group performance
     */
    public function testDocParsePerformance()
    {
        $imports = [
            'ignorephpdoc'     => 'Annotations\Annotation\IgnorePhpDoc',
            'ignoreannotation' => 'Annotations\Annotation\IgnoreAnnotation',
            'route'            => Fixtures\Annotation\Route::class,
            'template'         => Fixtures\Annotation\Template::class,
            '__NAMESPACE__'    => 'Doctrine\Tests\Common\Annotations\Fixtures',
        ];
        $ignored = [
            'access', 'author', 'copyright', 'deprecated', 'example', 'ignore',
            'internal', 'link', 'see', 'since', 'tutorial', 'version', 'package',
            'subpackage', 'name', 'global', 'param', 'return', 'staticvar',
            'static', 'var', 'throws', 'inheritdoc',
        ];

        $method = $this->getMethod();
        $methodComment = $method->getDocComment();
        $classComment = $method->getDeclaringClass()->getDocComment();

        $time = microtime(true);
        for ($i=0,$c=200; $i<$c; $i++) {
            $parser = new DocParser();
            $parser->setImports($imports);
            $parser->setIgnoredAnnotationNames(array_fill_keys($ignored, true));
            $parser->setIgnoreNotImportedAnnotations(true);

            $parser->parse($methodComment);
            $parser->parse($classComment);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-parser', $time, $c);
    }

    /**
     * @group performance
     */
    public function testDocLexerPerformance()
    {
        $method = $this->getMethod();
        $methodComment = $method->getDocComment();
        $classComment = $method->getDeclaringClass()->getDocComment();

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $lexer = new DocLexer();
            $lexer->setInput($methodComment);
            $lexer->setInput($classComment);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-lexer', $time, $c);
    }

    /**
     * @group performance
     */
    public function testPhpParserPerformanceWithShortCut()
    {
        $class = new \ReflectionClass(Fixtures\NamespacedSingleClassLOC1000::class);

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $parser = new PhpParser();
            $parser->parseClass($class);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-parser-with-short-cut', $time, $c);
    }

    /**
     * @group performance
     */
    public function testPhpParserPerformanceWithoutShortCut()
    {
        $class = new \ReflectionClass(\SingleClassLOC1000::class);

        $time = microtime(true);
        for ($i=0,$c=500; $i<$c; $i++) {
            $parser = new PhpParser();
            $parser->parseClass($class);
        }
        $time = microtime(true) - $time;

        $this->printResults('doc-parser-without-short-cut', $time, $c);
    }

    private function getMethod()
    {
        return new \ReflectionMethod(Fixtures\Controller::class, 'helloAction');
    }

    private function printResults($test, $time, $iterations)
    {
        if (! $iterations) {
            throw new \InvalidArgumentException('$iterations cannot be zero.');
        }

        $title = $test." results:\n";
        $iterationsText = sprintf("Iterations:         %d\n", $iterations);
        $totalTime      = sprintf("Total Time:         %.3f s\n", $time);
        $iterationTime  = sprintf("Time per iteration: %.3f ms\n", $time/$iterations * 1000);

        $max = max(strlen($title), strlen($iterationTime)) - 1;

        echo "\n".str_repeat('-', $max)."\n";
        echo $title;
        echo str_repeat('=', $max)."\n";
        echo $iterationsText;
        echo $totalTime;
        echo $iterationTime;
        echo str_repeat('-', $max)."\n";
    }
}
