<?php

namespace Doctrine\AnnotationsTests\Parser;


use ReflectionClass;

use Doctrine\AnnotationsTests\Fixtures\DummyClass;
use Doctrine\Annotations\Parser\PhpParser;

require_once __DIR__ . '/../Fixtures/NonNamespacedClass.php';
require_once __DIR__ . '/../Fixtures/GlobalNamespacesPerFileWithClassAsFirst.php';
require_once __DIR__ . '/../Fixtures/GlobalNamespacesPerFileWithClassAsLast.php';

class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidReflectionClass()
    {
        $class = new DummyClass();
        $parser = new PhpParser();

        $parser->parse($class);
    }

    public function testParseWithMultipleClassesInFile()
    {
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\MultipleClassesInFile');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parse($class));
    }

    public function testParseWithMultipleImportsInUseStatement()
    {
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\MultipleImportsInUseStatement');
        $parser = new PhpParser();

        $this->assertEquals(array(
            'route'  => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parse($class));
    }

    public function testParseWhenNotUserDefined()
    {
        $parser = new PhpParser();
        $this->assertEquals(array(), $parser->parse(new \ReflectionClass('\stdClass')));
    }

    public function testClassFileDoesNotExist()
    {
        $class = $this->getMockBuilder('\ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();

        $class->expects($this->once())
            ->method('getFilename')
            ->willReturn('/valid/class/Fake.php(35) : eval()d code');

        $class->expects($this->once())
            ->method('getStartLine')
            ->willReturn(10);

        $parser = new PhpParser();
        $result = $parser->parse($class);

        $this->assertEquals([], $result);
    }

    public function testParseWhenClassIsNotNamespaced()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('\AnnotationsTestsFixturesNonNamespacedClass');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testParseWhenClassIsInterface()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\TestInterface');

        $this->assertEquals(array(
            'secure' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parse($class));
    }

    public function testClassWithFullyQualifiedUseStatements()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithFullyQualifiedUseStatements');

        $this->assertEquals(array(
            'secure'   => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testNamespaceAndClassCommentedOut()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceAndClassCommentedOut');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
	}

    public function testEqualNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\EqualNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
        ), $parser->parse($class));
    }

    public function testEqualNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\EqualNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\DifferentNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
        ), $parser->parse($class));
    }

    public function testDifferentNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\DifferentNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsFirst()
    {
        $parser = new PhpParser();
        $class = new \ReflectionClass('\GlobalNamespacesPerFileWithClassAsFirst');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
        ), $parser->parse($class));
    }

    public function testGlobalNamespacesPerFileWithClassAsLast()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('\GlobalNamespacesPerFileWithClassAsLast');

        $this->assertEquals(array(
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testNamespaceWithClosureDeclaration()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceWithClosureDeclaration');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
    }

    public function testIfPointerResetsOnMultipleParsingTries()
    {
        $parser = new PhpParser();
        $class = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\NamespaceWithClosureDeclaration');

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));

        $this->assertEquals(array(
            'secure'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Secure',
            'route'    => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
        ), $parser->parse($class));
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
        ), $parser->parse($class));
    }
}