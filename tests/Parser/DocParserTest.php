<?php

namespace Doctrine\AnnotationsTests\Parser;

use Doctrine\AnnotationsTests\TestCase;

use Doctrine\Annotations\Context;
use Doctrine\Annotations\Builder;
use Doctrine\Annotations\Resolver;

use Doctrine\Annotations\Parser\DocParser;
use Doctrine\Annotations\Annotation\Target;

use Doctrine\AnnotationsTests\Fixtures\Parser\Name;
use Doctrine\AnnotationsTests\Fixtures\Parser\Marker;

use Doctrine\AnnotationsTests\Fixtures\Parser\SettingsAnnotation;
use Doctrine\AnnotationsTests\Fixtures\Parser\AnnotationWithTargetEmpty;
use Doctrine\AnnotationsTests\Fixtures\Parser\AnnotationExtendsAnnotationTargetAll;
use Doctrine\AnnotationsTests\Fixtures\Parser\AnnotationWithInvalidTargetDeclaration;
use Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationClassNameWithoutConstructor;
use Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationWithConstructorWithoutParams;
use Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationClassNameWithoutConstructorAndProperties;

use Doctrine\AnnotationsTests\Fixtures\ClassWithConstants;
use Doctrine\AnnotationsTests\Fixtures\IntefaceWithConstants;
use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants;

class DocParserTest extends TestCase
{
    public function testNestedArraysWithNestedAnnotation()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        // Nested arrays with nested annotations
        $result = $parser->parse('@Name(foo={1,2, {"key"=@Name}})', $context);
        $annot = $result[0];

        $this->assertInstanceOf(Name::CLASS, $annot);
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
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        // Marker annotation
        $result = $parser->parse("@Name", $context);
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertNull($annot->value);
        $this->assertNull($annot->foo);

        // Associative arrays
        $result = $parser->parse('@Name(foo={"key1" = "value1"})', $context);
        $annot = $result[0];
        $this->assertNull($annot->value);
        $this->assertTrue(is_array($annot->foo));
        $this->assertTrue(isset($annot->foo['key1']));
        // Numerical arrays
        $result = $parser->parse('@Name({2="foo", 4="bar"})', $context);
        $annot = $result[0];
        $this->assertTrue(is_array($annot->value));
        $this->assertEquals('foo', $annot->value[2]);
        $this->assertEquals('bar', $annot->value[4]);
        $this->assertFalse(isset($annot->value[0]));
        $this->assertFalse(isset($annot->value[1]));
        $this->assertFalse(isset($annot->value[3]));

        // Multiple values
        $result = $parser->parse('@Name(@Name, @Name)', $context);
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertTrue($annot->value[0] instanceof Name);
        $this->assertTrue($annot->value[1] instanceof Name);

        // Multiple types as values
        $result = $parser->parse('@Name(foo="Bar", @Name, {"key1"="value1", "key2"="value2"})', $context);
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertTrue($annot->value[0] instanceof Name);
        $this->assertTrue(is_array($annot->value[1]));
        $this->assertEquals('value1', $annot->value[1]['key1']);
        $this->assertEquals('value2', $annot->value[1]['key2']);

        // Complete docblock
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [], [], true);
        $docblock = <<<DOCBLOCK
/**
 * Some nifty class.
 *
 * @author Mr.X
 * @Name(foo="bar")
 */
DOCBLOCK;

        $result = $parser->parse($docblock, $context);
        $this->assertEquals(1, count($result));
        $annot = $result[0];
        $this->assertTrue($annot instanceof Name);
        $this->assertEquals("bar", $annot->foo);
        $this->assertNull($annot->value);
   }

    public function testDefaultValueAnnotations()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        // Array as first value
        $result = $parser->parse('@Name({"key1"="value1"})', $context);
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertEquals('value1', $annot->value['key1']);

        // Array as first value and additional values
        $result = $parser->parse('@Name({"key1"="value1"}, foo="bar")', $context);
        $annot = $result[0];

        $this->assertTrue($annot instanceof Name);
        $this->assertTrue(is_array($annot->value));
        $this->assertEquals('value1', $annot->value['key1']);
        $this->assertEquals('bar', $annot->foo);
    }

    public function testNamespacedAnnotations()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [], [], true);
        $docblock = <<<DOCBLOCK
/**
 * Some nifty class.
 *
 * @package foo
 * @subpackage bar
 * @author Mr.X <mr@x.com>
 * @Doctrine\AnnotationsTests\Fixtures\Parser\Name(foo="bar")
 * @ignore
 */
DOCBLOCK;

        $result = $parser->parse($docblock, $context);
        $this->assertCount(1, $result);
        $annot = $result[0];
        $this->assertInstanceOf(Name::CLASS, $annot);
        $this->assertEquals("bar", $annot->foo);
    }

    /**
     * @group debug
     */
    public function testTypicalMethodDocBlock()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [], [], true);
        $docblock = <<<DOCBLOCK
/**
 * Some nifty method.
 *
 * @since 2.0
 * @Doctrine\AnnotationsTests\Fixtures\Parser\Name(foo="bar")
 * @param string \$foo This is foo.
 * @param mixed \$bar This is bar.
 * @return string Foo and bar.
 * @This is irrelevant
 * @Marker
 */
DOCBLOCK;

        $result = $parser->parse($docblock, $context);
        $this->assertCount(2, $result);
        $this->assertTrue(isset($result[0]));
        $this->assertTrue(isset($result[1]));
        $annot = $result[0];
        $this->assertInstanceOf(Name::CLASS, $annot);
        $this->assertEquals("bar", $annot->foo);
        $marker = $result[1];
        $this->assertInstanceOf(Marker::CLASS, $marker);
    }


    public function testAnnotationWithoutConstructor()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@SomeAnnotationClassNameWithoutConstructor("Some data")';

        $result     = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertNotNull($annot);
        $this->assertInstanceOf(SomeAnnotationClassNameWithoutConstructor::CLASS, $annot);

        $this->assertNull($annot->name);
        $this->assertNotNull($annot->data);
        $this->assertEquals($annot->data, "Some data");

        $docblock   = '@SomeAnnotationClassNameWithoutConstructor(name="Some Name", data = "Some data")';
        $result     = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof SomeAnnotationClassNameWithoutConstructor);

        $this->assertEquals($annot->name, "Some Name");
        $this->assertEquals($annot->data, "Some data");

        $docblock = '@SomeAnnotationClassNameWithoutConstructor(data = "Some data")';
        $result   = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertEquals($annot->data, "Some data");
        $this->assertNull($annot->name);


        $docblock = '@SomeAnnotationClassNameWithoutConstructor(name = "Some name")';
        $result   = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertEquals($annot->name, "Some name");
        $this->assertNull($annot->data);

        $docblock = '@SomeAnnotationClassNameWithoutConstructor("Some data")';
        $result   = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertEquals($annot->data, "Some data");
        $this->assertNull($annot->name);



        $docblock   = '@SomeAnnotationClassNameWithoutConstructor("Some data",name = "Some name")';
        $result     = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertEquals($annot->name, "Some name");
        $this->assertEquals($annot->data, "Some data");


        $docblock   = '@SomeAnnotationWithConstructorWithoutParams(name = "Some name")';
        $result     = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $annot      = $result[0];

        $this->assertEquals($annot->name, "Some name");
        $this->assertEquals($annot->data, "Some data");

        $docblock = '@SomeAnnotationClassNameWithoutConstructorAndProperties()';
        $result   = $parser->parse($docblock, $context);
        $this->assertEquals(count($result), 1);
        $this->assertTrue($result[0] instanceof SomeAnnotationClassNameWithoutConstructorAndProperties);
    }

    public function testAnnotationTarget()
    {
        $imports = [
            'annotationtargetall'            => 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll',
            'annotationtargetclass'          => 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass',
            'annotationtargetpropertymethod' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod',
        ];

        $class    = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget');
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget', 'name');
        $method   = new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget', 'someFunction');

        $docComment      = '@AnnotationTargetAll';
        $parser          = $this->createTestParser();
        $classContext    = $this->createTestContext($class, null, $imports);
        $methodContext   = $this->createTestContext($method, null, $imports);
        $propertyContext = $this->createTestContext($property, null, $imports);

        $classResult    = $parser->parse($docComment, $classContext);
        $methodResult   = $parser->parse($docComment, $methodContext);
        $propertyResult = $parser->parse($docComment, $propertyContext);

        $this->assertCount(1, $classResult);
        $this->assertCount(1, $methodResult);
        $this->assertCount(1, $propertyResult);


        try {
            $parser->parse('@AnnotationTargetPropertyMethod', $classContext);

            $this->fail('Fail to valid annotation target');
        } catch (\Doctrine\Annotations\Exception\TargetNotAllowedException $exc) {
            $this->assertEquals('Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod is not allowed to be declared on class Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget. You may only use this annotation on these code elements: METHOD,PROPERTY.', $exc->getMessage());
        }

        try {
            $parser->parse('@AnnotationTargetClass', $propertyContext);

            $this->fail('Fail to valid annotation target');
        } catch (\Doctrine\Annotations\Exception\TargetNotAllowedException $exc) {
            $this->assertEquals('Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass is not allowed to be declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget::$name. You may only use this annotation on these code elements: CLASS.', $exc->getMessage());
        }

        try {
            $parser->parse('@AnnotationTargetClass', $methodContext);

            $this->fail('Fail to valid annotation target');
        } catch (\Doctrine\Annotations\Exception\TargetNotAllowedException $exc) {
            $this->assertEquals('Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass is not allowed to be declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget::someFunction(). You may only use this annotation on these code elements: CLASS.', $exc->getMessage());
        }
    }

    public function getAnnotationVarTypeProviderValid()
    {
        //({attribute name}, {attribute value})
         return array(
            // mixed type
            array('mixed', '"String Value"'),
            array('mixed', 'true'),
            array('mixed', 'false'),
            array('mixed', '1'),
            array('mixed', '1.2'),
            array('mixed', '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll'),

            // boolean type
            array('boolean', 'true'),
            array('boolean', 'false'),

            // alias for internal type boolean
            array('bool', 'true'),
            array('bool', 'false'),

            // integer type
            array('integer', '0'),
            array('integer', '1'),
            array('integer', '123456789'),
            array('integer', '9223372036854775807'),

            // alias for internal type double
            array('float', '0.1'),
            array('float', '1.2'),
            array('float', '123.456'),

            // string type
            array('string', '"String Value"'),
            array('string', '"true"'),
            array('string', '"123"'),

              // array type
            array('array', '{@AnnotationExtendsAnnotationTargetAll}'),
            array('array', '{@AnnotationExtendsAnnotationTargetAll,@AnnotationExtendsAnnotationTargetAll}'),

            array('arrayOfIntegers', '1'),
            array('arrayOfIntegers', '{1}'),
            array('arrayOfIntegers', '{1,2,3,4}'),
            array('arrayOfAnnotations', '@AnnotationExtendsAnnotationTargetAll'),
            array('arrayOfAnnotations', '{@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll}'),
            array('arrayOfAnnotations', '{@AnnotationExtendsAnnotationTargetAll, @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll}'),

            // annotation instance
            array('annotation', '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll'),
            array('annotation', '@AnnotationExtendsAnnotationTargetAll'),
        );
    }

    public function getAnnotationVarTypeProviderInvalid()
    {
         //({attribute name}, {type declared type}, {attribute value} , {given type or class})
         return array(
            // boolean type
            array('boolean','boolean','1','integer'),
            array('boolean','boolean','1.2','double'),
            array('boolean','boolean','"str"','string'),
            array('boolean','boolean','{1,2,3}','array'),
            array('boolean','boolean','@Name', 'an instance of Doctrine\AnnotationsTests\Fixtures\Parser\Name'),

            // alias for internal type boolean
            array('bool','boolean', '1','integer'),
            array('bool','boolean', '1.2','double'),
            array('bool','boolean', '"str"','string'),
            array('bool','boolean', '{"str"}','array'),

            // integer type
            array('integer','integer', 'true','boolean'),
            array('integer','integer', 'false','boolean'),
            array('integer','integer', '1.2','double'),
            array('integer','integer', '"str"','string'),
            array('integer','integer', '{"str"}','array'),
            array('integer','integer', '{1,2,3,4}','array'),

            // alias for internal type double
            array('float','double', 'true','boolean'),
            array('float','double', 'false','boolean'),
            array('float','double', '123','integer'),
            array('float','double', '"str"','string'),
            array('float','double', '{"str"}','array'),
            array('float','double', '{12.34}','array'),
            array('float','double', '{1,2,3}','array'),

            // string type
            array('string','string', 'true','boolean'),
            array('string','string', 'false','boolean'),
            array('string','string', '12','integer'),
            array('string','string', '1.2','double'),
            array('string','string', '{"str"}','array'),
            array('string','string', '{1,2,3,4}','array'),

             // annotation instance
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', 'true','boolean'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', 'false','boolean'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '12','integer'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '1.2','double'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{"str"}','array'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{1,2,3,4}','array'),
            array('annotation','Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '@Name','an instance of Doctrine\AnnotationsTests\Fixtures\Parser\Name'),
        );
    }

    public function getAnnotationVarTypeArrayProviderInvalid()
    {
         //({attribute name}, {type declared type}, {attribute value} , {given type or class})
         return array(
            array('arrayOfIntegers', 'integer', 'true', 'boolean'),
            array('arrayOfIntegers', 'integer', 'false', 'boolean'),
            array('arrayOfIntegers', 'integer', '{true,true}', 'boolean'),
            array('arrayOfIntegers', 'integer', '{1,true}', 'boolean'),
            array('arrayOfIntegers', 'integer', '{1,2,1.2}', 'double'),
            array('arrayOfIntegers', 'integer', '{1,2,"str"}', 'string'),

            array('arrayOfStrings', 'string', 'true', 'boolean'),
            array('arrayOfStrings', 'string', 'false', 'boolean'),
            array('arrayOfStrings', 'string', '{true,true}', 'boolean'),
            array('arrayOfStrings', 'string', '{"foo",true}', 'boolean'),
            array('arrayOfStrings', 'string', '{"foo","bar",1.2}', 'double'),
            array('arrayOfStrings', 'string', '1', 'integer'),

            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', 'true', 'boolean'),
            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', 'false', 'boolean'),
            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll,true}', 'boolean'),
            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll,true}', 'boolean'),
            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll,1.2}', 'double'),
            array('arrayOfAnnotations', 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', '{@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll,@AnnotationExtendsAnnotationTargetAll,"str"}', 'string'),
        );
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderValid
     */
    public function testAnnotationWithVarType($attribute, $value)
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = sprintf('@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType(%s = %s)',$attribute, $value);

        $result = $parser->parse($docblock, $context);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType', $result[0]);
        $this->assertNotNull($result[0]->$attribute);
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderInvalid
     */
    public function testAnnotationWithVarTypeError($attribute, $type, $value, $given)
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = sprintf('@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType(%s = %s)', $attribute, $value);

        try {
            $parser->parse($docblock, $context);
            $this->fail('Fail to validate type for property ' . var_export($attribute, true));
        } catch (\Doctrine\Annotations\Exception\TypeMismatchException $exc) {
            $this->assertEquals("Attribute \"$attribute\" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass expects a(n) $type, but got $given.", $exc->getMessage());
        }
    }


    /**
     * @dataProvider getAnnotationVarTypeArrayProviderInvalid
     */
    public function testAnnotationWithVarTypeArrayError($attribute,$type,$value,$given)
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = sprintf('@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType(%s = %s)',$attribute, $value);

        try {
            $parser->parse($docblock, $context);
            $this->fail('Fail to validate array type for property ' . var_export($attribute, true));
        } catch (\Doctrine\Annotations\Exception\TypeMismatchException $exc) {
            $this->assertEquals("Attribute \"$attribute\" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass expects either a(n) $type, or an array of {$type}s, but got $given.", $exc->getMessage());
        }
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TypeMismatchException
     * @expectedExceptionMessage Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass accept only [ONE, TWO, THREE], but got FOUR
     */
    public function testAnnotationEnumeratorException()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum("FOUR")';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TypeMismatchException
     * @expectedExceptionMessage Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnumLiteral declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass accept only [AnnotationEnumLiteral::ONE, AnnotationEnumLiteral::TWO, AnnotationEnumLiteral::THREE], but got 4.
     */
    public function testAnnotationEnumeratorLiteralException()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnumLiteral(4)';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage @Enum supports only scalar values "array" given.
     */
    public function testAnnotationEnumInvalidTypeDeclarationException()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnumInvalid("foo")';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Undefined enumerator value "3" for literal "AnnotationEnumLiteral::THREE".
     */
    public function testAnnotationEnumInvalidLiteralDeclarationException()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnumLiteralInvalid("foo")';

        $parser->parse($docblock, $context);
    }

    public function getConstantsProvider()
    {
        $provider[] = array(
            '@AnnotationWithConstants(PHP_EOL)',
            PHP_EOL
        );
        $provider[] = array(
            '@AnnotationWithConstants(AnnotationWithConstants::INTEGER)',
            AnnotationWithConstants::INTEGER
        );
        $provider[] = array(
            '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants(AnnotationWithConstants::STRING)',
            AnnotationWithConstants::STRING
        );
        $provider[] = array(
            '@AnnotationWithConstants(Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants::FLOAT)',
            AnnotationWithConstants::FLOAT
        );
        $provider[] = array(
            '@AnnotationWithConstants(ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE
        );
        $provider[] = array(
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_)',
            ClassWithConstants::OTHER_KEY_
        );
        $provider[] = array(
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_2)',
            ClassWithConstants::OTHER_KEY_2
        );
        $provider[] = array(
            '@AnnotationWithConstants(Doctrine\AnnotationsTests\Fixtures\ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE
        );
        $provider[] = array(
            '@AnnotationWithConstants(IntefaceWithConstants::SOME_VALUE)',
            IntefaceWithConstants::SOME_VALUE
        );
        $provider[] = array(
            '@AnnotationWithConstants(\Doctrine\AnnotationsTests\Fixtures\IntefaceWithConstants::SOME_VALUE)',
            IntefaceWithConstants::SOME_VALUE
        );
        $provider[] = array(
            '@AnnotationWithConstants({AnnotationWithConstants::STRING, AnnotationWithConstants::INTEGER, AnnotationWithConstants::FLOAT})',
            array(AnnotationWithConstants::STRING, AnnotationWithConstants::INTEGER, AnnotationWithConstants::FLOAT)
        );
        $provider[] = array(
            '@AnnotationWithConstants({
                AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER
             })',
            array(AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER)
        );
        $provider[] = array(
            '@AnnotationWithConstants({
                Doctrine\AnnotationsTests\Fixtures\IntefaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
             })',
            array(IntefaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER)
        );
        $provider[] = array(
            '@AnnotationWithConstants({
                \Doctrine\AnnotationsTests\Fixtures\IntefaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
             })',
            array(IntefaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER)
        );
        $provider[] = array(
            '@AnnotationWithConstants({
                AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER,
                ClassWithConstants::SOME_KEY = ClassWithConstants::SOME_VALUE,
                Doctrine\AnnotationsTests\Fixtures\ClassWithConstants::SOME_KEY = IntefaceWithConstants::SOME_VALUE
             })',
            array(
                AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER,
                ClassWithConstants::SOME_KEY    => ClassWithConstants::SOME_VALUE,
                ClassWithConstants::SOME_KEY    => IntefaceWithConstants::SOME_VALUE
            )
        );
        $provider[] = array(
            '@AnnotationWithConstants(AnnotationWithConstants::class)',
            'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants'
        );
        $provider[] = array(
            '@AnnotationWithConstants({AnnotationWithConstants::class = AnnotationWithConstants::class})',
            array('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants')
        );
        $provider[] = array(
            '@AnnotationWithConstants(Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants::class)',
            'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants'
        );
        $provider[] = array(
            '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants(Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants::class)',
            'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants'
        );
        return $provider;
    }

    /**
     * @dataProvider getConstantsProvider
     */
    public function testSupportClassConstants($docblock, $expected)
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [
            'classwithconstants'        => 'Doctrine\AnnotationsTests\Fixtures\ClassWithConstants',
            'intefacewithconstants'     => 'Doctrine\AnnotationsTests\Fixtures\IntefaceWithConstants',
            'annotationwithconstants'   => 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants'
        ]);

        $result = $parser->parse($docblock, $context);
        $this->assertInstanceOf('\Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithConstants', $annotation = $result[0]);
        $this->assertEquals($expected, $annotation->value);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The annotation @Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationClassNameWithoutConstructorAndProperties declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass does not accept any values, but got {"value":"Foo"}.
     */
    public function testWithoutConstructorWhenIsNotDefaultValue()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@SomeAnnotationClassNameWithoutConstructorAndProperties("Foo")';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The annotation @Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationClassNameWithoutConstructorAndProperties declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass does not accept any values, but got {"value":"Foo"}.
     */
    public function testWithoutConstructorWhenHasNoProperties()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@SomeAnnotationClassNameWithoutConstructorAndProperties(value = "Foo")';

       $parser->parse($docblock, $context);
    }

    /**
     * @expectedException Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Unrecognized token ")"
     */
    public function testAnnotationTargetSyntaxError()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithTargetSyntaxError()';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Target "Foo". Available targets: [ALL, CLASS, METHOD, PROPERTY, ANNOTATION]
     */
    public function testAnnotationWithInvalidTargetDeclarationError()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@AnnotationWithInvalidTargetDeclaration()';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage @Target expects either a string value, or an array of strings, "NULL" given.
     */
    public function testAnnotationWithTargetEmptyError()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@AnnotationWithTargetEmpty()';

        $parser->parse($docblock, $context);
    }

    /**
     * @group DDC-575
     */
    public function testRegressionDDC575()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = <<<DOCBLOCK
/**
 * @Name
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock, $context);

        $this->assertInstanceOf("Doctrine\AnnotationsTests\Fixtures\Parser\Name", $result[0]);

        $docblock = <<<DOCBLOCK
/**
 * @Name
 * @Marker
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock, $context);

        $this->assertInstanceOf("Doctrine\AnnotationsTests\Fixtures\Parser\Name", $result[0]);
    }

    /**
     * @group DDC-77
     */
    public function testAnnotationWithoutClassIsIgnoredWithoutWarning()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext(null, null, [], [], true);
        $result  = $parser->parse("@param", $context);

        $this->assertEquals(0, count($result));
    }

    /**
     * @group DCOM-168
     */
    public function testNotAnAnnotationClassIsIgnoredWithoutWarning()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext(null, null, [], [
            'PHPUnit_Framework_TestCase' => true
        ]);

        $result = $parser->parse('@PHPUnit_Framework_TestCase', $context);

        $this->assertCount(0, $result);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Unrecognized token "'" at line 1 and column 11
     */
    public function testAnnotationDontAcceptSingleQuotes()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $parser->parse("@Name(foo='bar')", $context);
    }

    /**
     * @group DCOM-41
     */
    public function testAnnotationDoesntThrowExceptionWhenAtSignIsNotFollowedByIdentifier()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $result  = $parser->parse("'@'", $context);

        $this->assertCount(0, $result);
    }

    /**
     * @group DCOM-41
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessageUnrecognized token "'" at line 1 and column 41
     */
    public function testAnnotationThrowsExceptionWhenAtSignIsNotFollowedByIdentifierInNestedAnnotation()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $parser->parse("@Doctrine\AnnotationsTests\Fixtures\Parser\Name(@')", $context);
    }

    public function createTestParser()
    {
        $builder   = $this->config->getBuilder();
        $resolver  = $this->config->getResolver();
        $hoaParser = $this->config->getHoaParser();
        $parser    = new DocParser($hoaParser, $builder, $resolver);

        return $parser;
    }

    public function createTestContext(\Reflector $reflection = null, string $namespace = null, array $imports = [], array $ignoredNames = [], bool $ignoreNotImported = false)
    {
        if ($reflection == null) {
            $reflection = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Parser\MyClass');
        }

        if ($namespace == null && $reflection instanceof \ReflectionClass) {
            $namespace = $reflection->getNamespaceName();
        }

        if ($namespace == null && ($reflection instanceof \ReflectionMethod || $reflection instanceof \ReflectionProperty)) {
            $namespace = $reflection->getDeclaringClass()->getNamespaceName();
        }

        if ($namespace == null) {
            $namespace = 'Doctrine\AnnotationsTests\Fixtures\Parser';
        }

        return new Context($reflection, [$namespace], $imports, $ignoredNames, $ignoreNotImported);
    }

    /**
     * @group DDC-78
     */
    public function testSyntaxErrorWithContextDescription()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $message = <<<TEXT
Fail to parse class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass
Unrecognized token "'" at line 1 and column 11:
@Name(foo='bar')
TEXT;

        $this->setExpectedException('\Doctrine\Annotations\Exception\ParserException', $message);

        $parser->parse("@Name(foo='bar')", $context);
    }

    /**
     * @group DDC-183
     */
    public function testSyntaxErrorWithUnknownCharacters()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [], [], true);
        $docblock = <<<DOCBLOCK
/****
 * @Entity
 * @test at.
 */
DOCBLOCK;

        $parser->parse($docblock, $context);
    }

    /**
     * @group DCOM-14
     */
    public function testIgnorePHPDocThrowTag()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext(null, null, [], [], true);
        $docblock = <<<DOCBLOCK
/**
 * @throws \RuntimeException
 */
DOCBLOCK;

        $parser->parse($docblock, $context);
    }

    /**
     * @group DCOM-38
     */
    public function testCastInt()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $result = $parser->parse("@Name(foo=1234)", $context);
        $annot  = $result[0];
        $this->assertInternalType('int', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeInt()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $result = $parser->parse("@Name(foo=-1234)", $context);
        $annot = $result[0];
        $this->assertInternalType('int', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastFloat()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $result = $parser->parse("@Name(foo=1234.345)", $context);
        $annot = $result[0];
        $this->assertInternalType('float', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeFloat()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $result = $parser->parse("@Name(foo=-1234.345)", $context);
        $annot = $result[0];
        $this->assertInternalType('float', $annot->foo);

        $result = $parser->parse("@Marker(-1234.345)", $context);
        $annot = $result[0];
        $this->assertInternalType('float', $annot->value);
    }

     /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The annotation @Doctrine\AnnotationsTests\Fixtures\Parser\SomeAnnotationClassNameWithoutConstructor declared on class Doctrine\AnnotationsTests\Fixtures\Parser\MyClass does not have a property named "invalidProperty". Available properties: data, name
     */
    public function testSetValuesExeption()
    {
        $parser   = $this->createTestParser();
        $context  = $this->createTestContext();
        $docblock = '@SomeAnnotationClassNameWithoutConstructor(invalidProperty = "Some val")';

        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ClassNotFoundException
     */
    public function testInvalidIdentifierInAnnotation()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $parser->parse('@Foo\3.42', $context);
    }

    public function testTrailingCommaIsAllowed()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $annots  = $parser->parse('@Name({
            "Foo",
            "Bar",
        })', $context);

        $this->assertEquals(1, count($annots));
        $this->assertEquals(array('Foo', 'Bar'), $annots[0]->value);
    }

    public function testDefaultAnnotationValueIsNotOverwritten()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $annots  = $parser->parse('@Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotWithDefaultValue', $context);

        $this->assertEquals(1, count($annots));
        $this->assertEquals('bar', $annots[0]->foo);
    }

    public function testArrayWithColon()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $annots  = $parser->parse('@Name({"foo": "bar"})', $context);

        $this->assertEquals(1, count($annots));
        $this->assertEquals(array('foo' => 'bar'), $annots[0]->value);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Couldn't find constant foo.
     */
    public function testInvalidContantName()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $parser->parse('@Name(foo)', $context);
    }

    /**
     * Tests parsing empty arrays.
     */
    public function testEmptyArray()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();

        $annots = $parser->parse('@Name({"foo": {}})', $context);
        $this->assertEquals(1, count($annots));
        $this->assertEquals(array('foo' => array()), $annots[0]->value);
    }

    public function testKeyHasNumber()
    {
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $annots  = $parser->parse('@SettingsAnnotation(foo="test", bar2="test")', $context);

        $this->assertEquals(1, count($annots));
        $this->assertEquals(array('foo' => 'test', 'bar2' => 'test'), $annots[0]->settings);
    }

    /**
     * @group 44
     */
    public function testSupportsEscapedQuotedValues()
    {
        $this->markTestIncomplete();
        $parser  = $this->createTestParser();
        $context = $this->createTestContext();
        $result  = $parser->parse('@Name(foo="""bar""")', $context);

        $this->assertCount(1, $result);

        $this->assertTrue($result[0] instanceof Name);
        $this->assertEquals('"bar"', $result[0]->foo);
    }
}
