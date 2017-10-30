<?php

namespace Doctrine\Tests\Common\Annotations;

use ReflectionClass;
use Doctrine\Common\Annotations\PhpParser;

require_once __DIR__.'/Fixtures/NonNamespacedClass.php';
require_once __DIR__.'/Fixtures/GlobalNamespacesPerFileWithClassAsFirst.php';
require_once __DIR__.'/Fixtures/GlobalNamespacesPerFileWithClassAsLast.php';

class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseClassWithMultipleClassesInFile() :void
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\MultipleClassesInFile');
        $parser = new PhpParser();

        self::assertEquals(array(
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testParseClassWithMultipleImportsInUseStatement() :void
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\MultipleImportsInUseStatement');
        $parser = new PhpParser();

        self::assertEquals(array(
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    /**
     * @requires PHP 7.0
     */
    public function testParseClassWithGroupUseStatement() :void
    {
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\GroupUseStatement');
        $parser = new PhpParser();

        self::assertEquals(array(
            'route'  => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'supersecure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenNotUserDefined() :void
    {
        $parser = new PhpParser();
        self::assertEquals(array(), $parser->parseClass(new \ReflectionClass(\stdClass::class)));
    }

    public function testClassFileDoesNotExist() :void
    {
        /* @var $class ReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
        $class = $this->getMockBuilder(ReflectionClass::class)
                ->disableOriginalConstructor()
                          ->getMock();
        $class->expects($this->once())
             ->method('getFilename')
             ->will($this->returnValue('/valid/class/Fake.php(35) : eval()d code'));

        $parser = new PhpParser();
        self::assertEquals(array(), $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsNotNamespaced() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(\AnnotationsTestsFixturesNonNamespacedClass::class);

        self::assertEquals(array(
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsInterface() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\TestInterface');

        self::assertEquals(array(
            'secure' => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testClassWithFullyQualifiedUseStatements() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\ClassWithFullyQualifiedUseStatements');

        self::assertEquals(array(
            'secure'   => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => '\\' . __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testNamespaceAndClassCommentedOut() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceAndClassCommentedOut');

        self::assertEquals(array(
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
	}

    public function testEqualNamespacesPerFileWithClassAsFirst() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\EqualNamespacesPerFileWithClassAsFirst');

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
        ), $parser->parseClass($class));
    }

    public function testEqualNamespacesPerFileWithClassAsLast() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\EqualNamespacesPerFileWithClassAsLast');

        self::assertEquals(array(
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsFirst() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\DifferentNamespacesPerFileWithClassAsFirst');

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsLast() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\DifferentNamespacesPerFileWithClassAsLast');

        self::assertEquals(array(
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsFirst() :void
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass(\GlobalNamespacesPerFileWithClassAsFirst::class);

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
        ), $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsLast() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(\GlobalNamespacesPerFileWithClassAsLast::class);

        self::assertEquals(array(
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testNamespaceWithClosureDeclaration() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceWithClosureDeclaration');

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testIfPointerResetsOnMultipleParsingTries() :void
    {
        $parser = new PhpParser();
        $class = new ReflectionClass(__NAMESPACE__ . '\Fixtures\NamespaceWithClosureDeclaration');

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));

        self::assertEquals(array(
            'secure'   => __NAMESPACE__ . '\Fixtures\Annotation\Secure',
            'route'    => __NAMESPACE__ . '\Fixtures\Annotation\Route',
            'template' => __NAMESPACE__ . '\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    /**
     * @group DCOM-97
     * @group regression
     */
    public function testClassWithClosure() :void
    {
        $parser = new PhpParser();
        $class  = new ReflectionClass(__NAMESPACE__ . '\Fixtures\ClassWithClosure');

        self::assertEquals(array(
          'annotationtargetall'         => __NAMESPACE__ . '\Fixtures\AnnotationTargetAll',
          'annotationtargetannotation'  => __NAMESPACE__ . '\Fixtures\AnnotationTargetAnnotation',
        ), $parser->parseClass($class));
    }
}
