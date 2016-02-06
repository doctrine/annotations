<?php

namespace Doctrine\AnnotationsTests\Parser;

use ReflectionClass;
use Doctrine\Annotations\Parser\PhpParser;

require_once __DIR__ . '/../Fixtures/NonNamespacedClass.php';
require_once __DIR__ . '/../Fixtures/GlobalNamespacesPerFileWithClassAsFirst.php';
require_once __DIR__ . '/../Fixtures/GlobalNamespacesPerFileWithClassAsLast.php';

class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseClassWithMultipleClassesInFile()
    {
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\MultipleClassesInFile');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testParseClassWithMultipleImportsInUseStatement()
    {
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\MultipleImportsInUseStatement');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenNotUserDefined()
    {
        $parser = new PhpParser();
        $this->assertEquals(array(), $parser->parseClass(new \ReflectionClass('\stdClass')));
    }

    public function testClassFileDoesNotExist()
    {
        $class = $this->getMockBuilder('\ReflectionClass')
                ->disableOriginalConstructor()
                          ->getMock();
        $class->expects($this->once())
             ->method('getFilename')
             ->will($this->returnValue('/valid/class/Fake.php(35) : eval()d code'));

        $parser = new PhpParser();
        $this->assertEquals(array(), $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsNotNamespaced()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('\AnnotationsTestsFixturesNonNamespacedClass');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testParseClassWhenClassIsInterface()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\TestInterface');

        $this->assertEquals(array(
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testClassWithFullyQualifiedUseStatements()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithFullyQualifiedUseStatements');

        $this->assertEquals(array(
            'secure'   => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testNamespaceAndClassCommentedOut()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceAndClassCommentedOut');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
	}

    public function testEqualNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\EqualNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
        ), $parser->parseClass($class));
    }

    public function testEqualNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\EqualNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\DifferentNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parseClass($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\DifferentNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass('\GlobalNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
        ), $parser->parseClass($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('\GlobalNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testNamespaceWithClosureDeclaration()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceWithClosureDeclaration');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    public function testIfPointerResetsOnMultipleParsingTries()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceWithClosureDeclaration');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parseClass($class));
    }

    /**
     * @group DCOM-97
     * @group regression
     */
    public function testClassWithClosure()
    {
        $parser = new PhpParser();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithClosure');

        $this->assertEquals(array(
          'annotationtargetall'         => 'Doctrine\AnnotationsTests\Fixtures\AnnotationTargetAll',
          'annotationtargetannotation'  => 'Doctrine\AnnotationsTests\Fixtures\AnnotationTargetAnnotation',
        ), $parser->parseClass($class));
    }
}