<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation\IgnorePhpDoc;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\AnnotationRegistry;

class DocParserTest extends \PHPUnit_Framework_TestCase
{
    public function testNestedArraysWithNestedAnnotation()
    {
        $parser = $this->createTestParser();

        // Nested arrays with nested annotations
        $result = $parser->parse('@Name(foo={1,2, {"key"=@Name}})');
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertNull($annot->value);
        $this->assertEquals(3, count($annot->foo));
        $this->assertEquals(1, $annot->foo[0]);
        $this->assertEquals(2, $annot->foo[1]);
        $this->assertTrue(is_array($annot->foo[2]));

        $nestedArray = $annot->foo[2];
        $this->assertTrue(isset($nestedArray['key']));
        $this->assertTrue($nestedArray['key'] instanceof Name);
    }

    public function testBasicAnnotations()
    {
        $parser = $this->createTestParser();

        // Marker annotation
        $result = $parser->parse("@Name");
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertNull($annot->value);
        $this->assertNull($annot->foo);

        // Associative arrays
        $result = $parser->parse('@Name(foo={"key1" = "value1"})');
        $annot = $result[0];
        $this->assertNull($annot->value);
        $this->assertTrue(is_array($annot->foo));
        $this->assertTrue(isset($annot->foo['key1']));

        // Numerical arrays
        $result = $parser->parse('@Name({2="foo", 4="bar"})');
        $annot = $result[0];
        $this->assertTrue(is_array($annot->value));
        $this->assertEquals('foo', $annot->value[2]);
        $this->assertEquals('bar', $annot->value[4]);
        $this->assertFalse(isset($annot->value[0]));
        $this->assertFalse(isset($annot->value[1]));
        $this->assertFalse(isset($annot->value[3]));

        // Multiple values
        $result = $parser->parse('@Name(@Name, @Name)');
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertTrue($annot->value[0] instanceof Name);
        $this->assertTrue($annot->value[1] instanceof Name);

        // Multiple types as values
        $result = $parser->parse('@Name(foo="Bar", @Name, {"key1"="value1", "key2"="value2"})');
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertTrue($annot->value[0] instanceof Name);
        $this->assertTrue(is_array($annot->value[1]));
        $this->assertEquals('value1', $annot->value[1]['key1']);
        $this->assertEquals('value2', $annot->value[1]['key2']);

        // Complete docblock
        $docblock = <<<DOCBLOCK
/**
 * Some nifty class.
 *
 * @author Mr.X
 * @Name(foo="bar")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        $this->assertEquals(1, count($result));
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertEquals("bar", $annot->foo);
        $this->assertNull($annot->value);
   }

    public function testNamespacedAnnotations()
    {
        $parser = new DocParser;
        $parser->setIgnoreNotImportedAnnotations(true);

        $docblock = <<<DOCBLOCK
/**
 * Some nifty class.
 *
 * @package foo
 * @subpackage bar
 * @author Mr.X <mr@x.com>
 * @Doctrine\Tests\Common\Annotations\Name(foo="bar")
 * @ignore
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        $this->assertEquals(1, count($result));
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertEquals("bar", $annot->foo);
    }

    /**
     * @group debug
     */
    public function testTypicalMethodDocBlock()
    {
        $parser = $this->createTestParser();

        $docblock = <<<DOCBLOCK
/**
 * Some nifty method.
 *
 * @since 2.0
 * @Doctrine\Tests\Common\Annotations\Name(foo="bar")
 * @param string \$foo This is foo.
 * @param mixed \$bar This is bar.
 * @return string Foo and bar.
 * @This is irrelevant
 * @Marker
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        $this->assertEquals(2, count($result));
        $this->assertTrue(isset($result[0]));
        $this->assertTrue(isset($result[1]));
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertEquals("bar", $annot->foo);
        $marker = $result[1];
        $this->assertTrue($marker instanceof Marker);
    }

    /**
     * @group DDC-575
     */
    public function testRegressionDDC575()
    {
        $parser = $this->createTestParser();

        $docblock = <<<DOCBLOCK
/**
 * @Name
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock);

        $this->assertInstanceOf("Doctrine\Tests\Common\Annotations\Name", $result[0]);

        $docblock = <<<DOCBLOCK
/**
 * @Name
 * @Marker
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock);

        $this->assertInstanceOf("Doctrine\Tests\Common\Annotations\Name", $result[0]);
    }

    /**
     * @group DDC-77
     */
    public function testAnnotationWithoutClassIsIgnoredWithoutWarning()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $result = $parser->parse("@param");

        $this->assertEquals(0, count($result));
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected PlainValue, got ''' at position 10.
     */
    public function testAnnotationDontAcceptSingleQuotes()
    {
        $parser = $this->createTestParser();
        $parser->parse("@Name(foo='bar')");
    }

    /**
     * @group DCOM-41
     */
    public function testAnnotationDoesntThrowExceptionWhenAtSignIsNotFollowedByIdentifier()
    {
        $parser = new DocParser();
        $result = $parser->parse("'@'");

        $this->assertEquals(0, count($result));
    }

    /**
     * @group DCOM-41
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     */
    public function testAnnotationThrowsExceptionWhenAtSignIsNotFollowedByIdentifierInNestedAnnotation()
    {
        $parser = new DocParser();
        $result = $parser->parse("@Doctrine\Tests\Common\Annotations\Name(@')");
    }
    
    /**
     * @group DCOM-56
     */
    public function testAutoloadAnnotation()
    {
        $this->assertFalse(class_exists('Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload', false), 'Pre-condition: Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload not allowed to be loaded.');
        
        $parser = new DocParser();
        
        AnnotationRegistry::registerAutoloadNamespace('Doctrine\Tests\Common\Annotations\Fixtures\Annotation', __DIR__ . '/../../../../');
        
        $parser->setImports(array(
            'autoload' => 'Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload',
        ));
        $annotations = $parser->parse('@Autoload');
        
        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload', $annotations[0]);
    }

    public function createTestParser()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $parser->setImports(array(
            'name' => 'Doctrine\Tests\Common\Annotations\Name',
            '__NAMESPACE__' => 'Doctrine\Tests\Common\Annotations',
        ));

        return $parser;
    }

    /**
     * @group DDC-78
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected PlainValue, got ''' at position 10 in class \Doctrine\Tests\Common\Annotations\Name
     */
    public function testSyntaxErrorWithContextDescription()
    {
        $parser = $this->createTestParser();
        $parser->parse("@Name(foo='bar')", "class \Doctrine\Tests\Common\Annotations\Name");
    }

    /**
     * @group DDC-183
     */
    public function testSyntaxErrorWithUnknownCharacters()
    {
        $docblock = <<<DOCBLOCK
/**
 * @test at.
 */
class A {
}
DOCBLOCK;

        //$lexer = new \Doctrine\Common\Annotations\Lexer();
        //$lexer->setInput(trim($docblock, '/ *'));
        //var_dump($lexer);

        try {
            $parser = $this->createTestParser();
            $result = $parser->parse($docblock);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group DCOM-14
     */
    public function testIgnorePHPDocThrowTag()
    {
        $docblock = <<<DOCBLOCK
/**
 * @throws \RuntimeException
 */
class A {
}
DOCBLOCK;

        try {
            $parser = $this->createTestParser();
            $result = $parser->parse($docblock);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group DCOM-38
     */
    public function testCastInt()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse("@Name(foo=1234)");
        $annot = $result[0];
        $this->assertInternalType('int', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastFloat()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse("@Name(foo=1234.345)");
        $annot = $result[0];
        $this->assertInternalType('float', $annot->foo);
    }

    public function testReservedKeywordsInAnnotations()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Doctrine\Tests\Common\Annotations\True');
        $this->assertTrue($result[0] instanceof True);
        $result = $parser->parse('@Doctrine\Tests\Common\Annotations\False');
        $this->assertTrue($result[0] instanceof False);
        $result = $parser->parse('@Doctrine\Tests\Common\Annotations\Null');
        $this->assertTrue($result[0] instanceof Null);

        $result = $parser->parse('@True');
        $this->assertTrue($result[0] instanceof True);
        $result = $parser->parse('@False');
        $this->assertTrue($result[0] instanceof False);
        $result = $parser->parse('@Null');
        $this->assertTrue($result[0] instanceof Null);
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Syntax Error] Expected Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER or Doctrine\Common\Annotations\DocLexer::T_TRUE or Doctrine\Common\Annotations\DocLexer::T_FALSE or Doctrine\Common\Annotations\DocLexer::T_NULL, got '3.42' at position 5.
     */
    public function testInvalidIdentifierInAnnotation()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Foo\3.42');
    }
}

class Name extends \Doctrine\Common\Annotations\Annotation {
    public $foo;
}

class Marker extends \Doctrine\Common\Annotations\Annotation {}

class True extends \Doctrine\Common\Annotations\Annotation {}

class False extends \Doctrine\Common\Annotations\Annotation {}

class Null extends \Doctrine\Common\Annotations\Annotation {}

namespace Doctrine\Tests\Common\Annotations\FooBar;

class Name extends \Doctrine\Common\Annotations\Annotation {
}
