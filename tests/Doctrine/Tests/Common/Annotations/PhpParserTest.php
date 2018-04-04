<?php

namespace Doctrine\Tests\Common\Annotations;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Doctrine\Common\Annotations\PhpParser;

require_once __DIR__.'/Fixtures/NonNamespacedClass.php';
require_once __DIR__.'/Fixtures/GlobalNamespacesPerFileWithClassAsFirst.php';
require_once __DIR__.'/Fixtures/GlobalNamespacesPerFileWithClassAsLast.php';

class PhpParserTest extends TestCase
{
    public function testParseClassWithMultipleClassesInFile()
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\MultipleClassesInFile');
        $parser = new PhpParser();

        self::assertEquals([
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ], $parser->parseClass($class));
    }

    public function testParseClassWithMultipleImportsInUseStatement()
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\MultipleImportsInUseStatement');
        $parser = new PhpParser();

        self::assertEquals([
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ], $parser->parseClass($class));
    }

    /**
     * @requires PHP 7.0
     */
    public function testParseClassWithGroupUseStatement()
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\GroupUseStatement');
        $parser = new PhpParser();

        self::assertEquals([
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'supersecure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testParseClassWhenNotUserDefined()
    {
        $parser = new PhpParser();
        self::assertEquals([], $parser->parseClass(new \ReflectionClass(\stdClass::class)));
    }

    public function testClassFileDoesNotExist()
    {
        /* @var $class ReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
        $class = $this->getMockBuilder(ReflectionClass::class)
                ->disableOriginalConstructor()
                          ->getMock();
        $class->expects($this->once())
             ->method('getFilename')
             ->will($this->returnValue('/valid/class/Fake.php(35) : eval()d code'));

        $parser = new PhpParser();
        self::assertEquals([], $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsNotNamespaced()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(\AnnotationsTestsFixturesNonNamespacedClass::class);

        self::assertEquals([
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsInterface()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\TestInterface');

        self::assertEquals([
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ], $parser->parseClass($class));
    }

    public function testClassWithFullyQualifiedUseStatements()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\ClassWithFullyQualifiedUseStatements');

        self::assertEquals([
            'secure'   => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testNamespaceAndClassCommentedOut()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceAndClassCommentedOut');

        self::assertEquals([
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
	}

    public function testEqualNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\EqualNamespacesPerFileWithClassAsFirst');

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
        ], $parser->parseClass($class));
    }

    public function testEqualNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\EqualNamespacesPerFileWithClassAsLast');

        self::assertEquals([
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\DifferentNamespacesPerFileWithClassAsFirst');

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ], $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\DifferentNamespacesPerFileWithClassAsLast');

        self::assertEquals([
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass(\GlobalNamespacesPerFileWithClassAsFirst::class);

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
        ], $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(\GlobalNamespacesPerFileWithClassAsLast::class);

        self::assertEquals([
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testNamespaceWithClosureDeclaration()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceWithClosureDeclaration');

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    public function testIfPointerResetsOnMultipleParsingTries()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceWithClosureDeclaration');

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));

        self::assertEquals([
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ], $parser->parseClass($class));
    }

    /**
     * @group DCOM-97
     * @group regression
     */
    public function testClassWithClosure()
    {
        $parser = new PhpParser();
        $class  = new ReflectionClass(__NAMESPACE__ . '\Fixtures\ClassWithClosure');

        self::assertEquals([
          'annotationtargetall'         => __NAMESPACE__ . '\Fixtures\AnnotationTargetAll',
          'annotationtargetannotation'  => __NAMESPACE__ . '\Fixtures\AnnotationTargetAnnotation',
        ], $parser->parseClass($class));
    }
}
