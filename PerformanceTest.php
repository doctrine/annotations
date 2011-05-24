<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\DocLexer;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\AnnotationReader;

require_once __DIR__ . '/Fixtures/Annotation/Route.php';
require_once __DIR__ . '/Fixtures/Annotation/Template.php';
require_once __DIR__ . '/Fixtures/Annotation/Secure.php';

class PerformanceTest extends \PHPUnit_Framework_TestCase
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
        $reader = new AnnotationReader();
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
        $imports = array(
            'ignorephpdoc'     => 'Annotations\Annotation\IgnorePhpDoc',
            'ignoreannotation' => 'Annotations\Annotation\IgnoreAnnotation',
            'route'            => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route',
            'template'         => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template',
            '__NAMESPACE__'    => 'Doctrine\Tests\Common\Annotations\Fixtures',
        );
        $ignored = array(
            'access', 'author', 'copyright', 'deprecated', 'example', 'ignore',
            'internal', 'link', 'see', 'since', 'tutorial', 'version', 'package',
            'subpackage', 'name', 'global', 'param', 'return', 'staticvar',
            'static', 'var', 'throws', 'inheritdoc',
        );

        $parser = new DocParser();
        $method = $this->getMethod();
        $methodComment = $method->getDocComment();
        $classComment = $method->getDeclaringClass()->getDocComment();

        $time = microtime(true);
        for ($i=0,$c=200; $i<$c; $i++) {
            $parser = new DocParser();
            $parser->setImports($imports);
            $parser->setIgnoredAnnotationNames($ignored);

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
        $lexer = new DocLexer();
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

    private function getMethod()
    {
        return new \ReflectionMethod('Doctrine\Tests\Common\Annotations\Fixtures\Controller', 'helloAction');
    }

    private function printResults($test, $time, $iterations)
    {
        if (0 == $iterations) {
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