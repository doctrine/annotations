<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithConstants;
use Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants;
use PHPUnit\Framework\TestCase;

class DocParserTest extends TestCase
{
    public function testNestedArraysWithNestedAnnotation()
    {
        $parser = $this->createTestParser();

        // Nested arrays with nested annotations
        $result = $parser->parse('@Name(foo={1,2, {"key"=@Name}})');
        $annot = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertNull($annot->value);
        self::assertCount(3, $annot->foo);
        self::assertEquals(1, $annot->foo[0]);
        self::assertEquals(2, $annot->foo[1]);
        self::assertInternalType('array', $annot->foo[2]);

        $nestedArray = $annot->foo[2];
        self::assertTrue(isset($nestedArray['key']));
        self::assertInstanceOf(Name::class, $nestedArray['key']);
    }

    public function testBasicAnnotations()
    {
        $parser = $this->createTestParser();

        // Marker annotation
        $result = $parser->parse('@Name');
        $annot = $result[0];
        self::assertInstanceOf(Name::class, $annot);
        self::assertNull($annot->value);
        self::assertNull($annot->foo);

        // Associative arrays
        $result = $parser->parse('@Name(foo={"key1" = "value1"})');
        $annot = $result[0];
        self::assertNull($annot->value);
        self::assertInternalType('array', $annot->foo);
        self::assertTrue(isset($annot->foo['key1']));

        // Numerical arrays
        $result = $parser->parse('@Name({2="foo", 4="bar"})');
        $annot = $result[0];
        self::assertInternalType('array', $annot->value);
        self::assertEquals('foo', $annot->value[2]);
        self::assertEquals('bar', $annot->value[4]);
        self::assertFalse(isset($annot->value[0]));
        self::assertFalse(isset($annot->value[1]));
        self::assertFalse(isset($annot->value[3]));

        // Multiple values
        $result = $parser->parse('@Name(@Name, @Name)');
        $annot = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertInternalType('array', $annot->value);
        self::assertInstanceOf(Name::class, $annot->value[0]);
        self::assertInstanceOf(Name::class, $annot->value[1]);

        // Multiple types as values
        $result = $parser->parse('@Name(foo="Bar", @Name, {"key1"="value1", "key2"="value2"})');
        $annot = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertInternalType('array', $annot->value);
        self::assertInstanceOf(Name::class, $annot->value[0]);
        self::assertInternalType('array', $annot->value[1]);
        self::assertEquals('value1', $annot->value[1]['key1']);
        self::assertEquals('value2', $annot->value[1]['key2']);

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
        self::assertCount(1, $result);
        $annot = $result[0];
        self::assertInstanceOf(Name::class, $annot);
        self::assertEquals('bar', $annot->foo);
        self::assertNull($annot->value);
   }

    public function testDefaultValueAnnotations()
    {
        $parser = $this->createTestParser();

        // Array as first value
        $result = $parser->parse('@Name({"key1"="value1"})');
        $annot = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertInternalType('array', $annot->value);
        self::assertEquals('value1', $annot->value['key1']);

        // Array as first value and additional values
        $result = $parser->parse('@Name({"key1"="value1"}, foo="bar")');
        $annot = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertInternalType('array', $annot->value);
        self::assertEquals('value1', $annot->value['key1']);
        self::assertEquals('bar', $annot->foo);
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
        self::assertCount(1, $result);
        $annot = $result[0];
        self::assertInstanceOf(Name::class, $annot);
        self::assertEquals('bar', $annot->foo);
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
        self::assertCount(2, $result);
        self::assertTrue(isset($result[0]));
        self::assertTrue(isset($result[1]));
        $annot = $result[0];
        self::assertInstanceOf(Name::class, $annot);
        self::assertEquals('bar', $annot->foo);
        $marker = $result[1];
        self::assertInstanceOf(Marker::class, $marker);
    }


    public function testAnnotationWithoutConstructor()
    {
        $parser = $this->createTestParser();


        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructor::class, $annot);

        self::assertNull($annot->name);
        self::assertNotNull($annot->data);
        self::assertEquals($annot->data, 'Some data');




$docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor(name="Some Name", data = "Some data")
 */
DOCBLOCK;


        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertNotNull($annot);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructor::class, $annot);

        self::assertEquals($annot->name, 'Some Name');
        self::assertEquals($annot->data, 'Some data');




$docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor(data = "Some data")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertEquals($annot->data, 'Some data');
        self::assertNull($annot->name);


        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor(name = "Some name")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertNull($annot->data);

        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertEquals($annot->data, 'Some data');
        self::assertNull($annot->name);



        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data",name = "Some name")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertEquals($annot->data, 'Some data');


        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationWithConstructorWithoutParams(name = "Some name")
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot      = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertEquals($annot->data, 'Some data');

        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties()
 */
DOCBLOCK;

        $result     = $parser->parse($docblock);
        self::assertCount(1, $result);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructorAndProperties::class, $result[0]);
    }

    public function testAnnotationTarget()
    {

        $parser = new DocParser;
        $parser->setImports([
            '__NAMESPACE__' => 'Doctrine\Tests\Common\Annotations\Fixtures',
        ]);
        $class  = new \ReflectionClass(Fixtures\ClassWithValidAnnotationTarget::class);


        $context    = 'class ' . $class->getName();
        $docComment = $class->getDocComment();

        $parser->setTarget(Target::TARGET_CLASS);
        self::assertNotNull($parser->parse($docComment,$context));


        $property   = $class->getProperty('foo');
        $docComment = $property->getDocComment();
        $context    = 'property ' . $class->getName() . "::\$" . $property->getName();

        $parser->setTarget(Target::TARGET_PROPERTY);
        self::assertNotNull($parser->parse($docComment,$context));



        $method     = $class->getMethod('someFunction');
        $docComment = $property->getDocComment();
        $context    = 'method ' . $class->getName() . '::' . $method->getName() . '()';

        $parser->setTarget(Target::TARGET_METHOD);
        self::assertNotNull($parser->parse($docComment,$context));


        try {
            $class      = new \ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtClass::class);
            $context    = 'class ' . $class->getName();
            $docComment = $class->getDocComment();

            $parser->setTarget(Target::TARGET_CLASS);
            $parser->parse($docComment, $context);

            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertNotNull($exc->getMessage());
        }


        try {

            $class      = new \ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtMethod::class);
            $method     = $class->getMethod('functionName');
            $docComment = $method->getDocComment();
            $context    = 'method ' . $class->getName() . '::' . $method->getName() . '()';

            $parser->setTarget(Target::TARGET_METHOD);
            $parser->parse($docComment, $context);

            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertNotNull($exc->getMessage());
        }


        try {
            $class      = new \ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class);
            $property   = $class->getProperty('foo');
            $docComment = $property->getDocComment();
            $context    = 'property ' . $class->getName() . "::\$" . $property->getName();

            $parser->setTarget(Target::TARGET_PROPERTY);
            $parser->parse($docComment, $context);

            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertNotNull($exc->getMessage());
        }

    }

    public function getAnnotationVarTypeProviderValid()
    {
        //({attribute name}, {attribute value})
         return [
            // mixed type
            ['mixed', '"String Value"'],
            ['mixed', 'true'],
            ['mixed', 'false'],
            ['mixed', '1'],
            ['mixed', '1.2'],
            ['mixed', '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll'],

            // boolean type
            ['boolean', 'true'],
            ['boolean', 'false'],

            // alias for internal type boolean
            ['bool', 'true'],
            ['bool', 'false'],

            // integer type
            ['integer', '0'],
            ['integer', '1'],
            ['integer', '123456789'],
            ['integer', '9223372036854775807'],

            // alias for internal type double
            ['float', '0.1'],
            ['float', '1.2'],
            ['float', '123.456'],

            // string type
            ['string', '"String Value"'],
            ['string', '"true"'],
            ['string', '"123"'],

              // array type
            ['array', '{@AnnotationExtendsAnnotationTargetAll}'],
            ['array', '{@AnnotationExtendsAnnotationTargetAll,@AnnotationExtendsAnnotationTargetAll}'],

            ['arrayOfIntegers', '1'],
            ['arrayOfIntegers', '{1}'],
            ['arrayOfIntegers', '{1,2,3,4}'],
            ['arrayOfAnnotations', '@AnnotationExtendsAnnotationTargetAll'],
            ['arrayOfAnnotations', '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll}'],
            ['arrayOfAnnotations', '{@AnnotationExtendsAnnotationTargetAll, @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll}'],

            // annotation instance
            ['annotation', '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll'],
            ['annotation', '@AnnotationExtendsAnnotationTargetAll'],
        ];
    }

    public function getAnnotationVarTypeProviderInvalid()
    {
         //({attribute name}, {type declared type}, {attribute value} , {given type or class})
         return [
            // boolean type
            ['boolean','boolean','1','integer'],
            ['boolean','boolean','1.2','double'],
            ['boolean','boolean','"str"','string'],
            ['boolean','boolean','{1,2,3}','array'],
            ['boolean','boolean','@Name', 'an instance of Doctrine\Tests\Common\Annotations\Name'],

            // alias for internal type boolean
            ['bool','bool', '1','integer'],
            ['bool','bool', '1.2','double'],
            ['bool','bool', '"str"','string'],
            ['bool','bool', '{"str"}','array'],

            // integer type
            ['integer','integer', 'true','boolean'],
            ['integer','integer', 'false','boolean'],
            ['integer','integer', '1.2','double'],
            ['integer','integer', '"str"','string'],
            ['integer','integer', '{"str"}','array'],
            ['integer','integer', '{1,2,3,4}','array'],

            // alias for internal type double
            ['float','float', 'true','boolean'],
            ['float','float', 'false','boolean'],
            ['float','float', '123','integer'],
            ['float','float', '"str"','string'],
            ['float','float', '{"str"}','array'],
            ['float','float', '{12.34}','array'],
            ['float','float', '{1,2,3}','array'],

            // string type
            ['string','string', 'true','boolean'],
            ['string','string', 'false','boolean'],
            ['string','string', '12','integer'],
            ['string','string', '1.2','double'],
            ['string','string', '{"str"}','array'],
            ['string','string', '{1,2,3,4}','array'],

             // annotation instance
            ['annotation', AnnotationTargetAll::class, 'true','boolean'],
            ['annotation', AnnotationTargetAll::class, 'false','boolean'],
            ['annotation', AnnotationTargetAll::class, '12','integer'],
            ['annotation', AnnotationTargetAll::class, '1.2','double'],
            ['annotation', AnnotationTargetAll::class, '{"str"}','array'],
            ['annotation', AnnotationTargetAll::class, '{1,2,3,4}','array'],
            ['annotation', AnnotationTargetAll::class, '@Name','an instance of Doctrine\Tests\Common\Annotations\Name'],
        ];
    }

    public function getAnnotationVarTypeArrayProviderInvalid()
    {
         //({attribute name}, {type declared type}, {attribute value} , {given type or class})
         return [
            ['arrayOfIntegers', 'integer', 'true', 'boolean'],
            ['arrayOfIntegers', 'integer', 'false', 'boolean'],
            ['arrayOfIntegers', 'integer', '{true,true}', 'boolean'],
            ['arrayOfIntegers', 'integer', '{1,true}', 'boolean'],
            ['arrayOfIntegers', 'integer', '{1,2,1.2}', 'double'],
            ['arrayOfIntegers', 'integer', '{1,2,"str"}', 'string'],

            ['arrayOfStrings', 'string', 'true', 'boolean'],
            ['arrayOfStrings', 'string', 'false', 'boolean'],
            ['arrayOfStrings', 'string', '{true,true}', 'boolean'],
            ['arrayOfStrings', 'string', '{"foo",true}', 'boolean'],
            ['arrayOfStrings', 'string', '{"foo","bar",1.2}', 'double'],
            ['arrayOfStrings', 'string', '1', 'integer'],

            ['arrayOfAnnotations', AnnotationTargetAll::class, 'true', 'boolean'],
            ['arrayOfAnnotations', AnnotationTargetAll::class, 'false', 'boolean'],
            ['arrayOfAnnotations', AnnotationTargetAll::class, '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,true}', 'boolean'],
            ['arrayOfAnnotations', AnnotationTargetAll::class, '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,true}', 'boolean'],
            ['arrayOfAnnotations', AnnotationTargetAll::class, '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,1.2}', 'double'],
            ['arrayOfAnnotations', AnnotationTargetAll::class, '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,@AnnotationExtendsAnnotationTargetAll,"str"}', 'string'],
        ];
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderValid
     */
    public function testAnnotationWithVarType($attribute, $value)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::$invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        $result = $parser->parse($docblock, $context);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithVarType::class, $result[0]);
        self::assertNotNull($result[0]->$attribute);
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderInvalid
     */
    public function testAnnotationWithVarTypeError($attribute,$type,$value,$given)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringMatchesFormat(
                '[Type Error] Attribute "' . $attribute . '" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType declared on property SomeClassName::invalidProperty. expects a(n) %A' . $type . ', but got ' . $given . '.',
                $exc->getMessage()
            );
        }
    }


    /**
     * @dataProvider getAnnotationVarTypeArrayProviderInvalid
     */
    public function testAnnotationWithVarTypeArrayError($attribute,$type,$value,$given)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringMatchesFormat(
                '[Type Error] Attribute "' . $attribute . '" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType declared on property SomeClassName::invalidProperty. expects either a(n) %A' . $type . ', or an array of %A' . $type . 's, but got ' . $given . '.',
                $exc->getMessage()
            );
        }
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderValid
     */
    public function testAnnotationWithAttributes($attribute, $value)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::$invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        $result = $parser->parse($docblock, $context);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithAttributes::class, $result[0]);
        $getter = 'get' .ucfirst($attribute);
        self::assertNotNull($result[0]->$getter());
    }

   /**
     * @dataProvider getAnnotationVarTypeProviderInvalid
     */
    public function testAnnotationWithAttributesError($attribute,$type,$value,$given)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains("[Type Error] Attribute \"$attribute\" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes declared on property SomeClassName::invalidProperty. expects a(n) $type, but got $given.", $exc->getMessage());
        }
    }


   /**
     * @dataProvider getAnnotationVarTypeArrayProviderInvalid
     */
    public function testAnnotationWithAttributesWithVarTypeArrayError($attribute,$type,$value,$given)
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = sprintf('@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',$attribute, $value);
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains("[Type Error] Attribute \"$attribute\" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes declared on property SomeClassName::invalidProperty. expects either a(n) $type, or an array of {$type}s, but got $given.", $exc->getMessage());
        }
    }

    public function testAnnotationWithRequiredAttributes()
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $parser->setTarget(Target::TARGET_PROPERTY);


        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes("Some Value", annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        $result     = $parser->parse($docblock);

        self::assertCount(1, $result);

        /* @var $annotation Fixtures\AnnotationWithRequiredAttributes */
        $annotation = $result[0];

        self::assertInstanceOf(Fixtures\AnnotationWithRequiredAttributes::class, $annotation);
        self::assertEquals('Some Value', $annotation->getValue());
        self::assertInstanceOf(Fixtures\AnnotationTargetAnnotation::class, $annotation->getAnnot());


        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes("Some Value")';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains('Attribute "annot" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes declared on property SomeClassName::invalidProperty. expects a(n) Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation. This value should not be null.', $exc->getMessage());
        }

        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes(annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains('Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes declared on property SomeClassName::invalidProperty. expects a(n) string. This value should not be null.', $exc->getMessage());
        }

    }

    public function testAnnotationWithRequiredAttributesWithoutConstructor()
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $parser->setTarget(Target::TARGET_PROPERTY);


        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor("Some Value", annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        $result     = $parser->parse($docblock);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithRequiredAttributesWithoutConstructor::class, $result[0]);
        self::assertEquals('Some Value', $result[0]->value);
        self::assertInstanceOf(Fixtures\AnnotationTargetAnnotation::class, $result[0]->annot);


        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor("Some Value")';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains('Attribute "annot" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor declared on property SomeClassName::invalidProperty. expects a(n) \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation. This value should not be null.', $exc->getMessage());
        }

        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor(annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertContains('Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor declared on property SomeClassName::invalidProperty. expects a(n) string. This value should not be null.', $exc->getMessage());
        }

    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum declared on property SomeClassName::invalidProperty. accept only [ONE, TWO, THREE], but got FOUR.
     */
    public function testAnnotationEnumeratorException()
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum("FOUR")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->setTarget(Target::TARGET_PROPERTY);
        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteral declared on property SomeClassName::invalidProperty. accept only [AnnotationEnumLiteral::ONE, AnnotationEnumLiteral::TWO, AnnotationEnumLiteral::THREE], but got 4.
     */
    public function testAnnotationEnumeratorLiteralException()
    {
        $parser     = $this->createTestParser();
        $context    = 'property SomeClassName::invalidProperty.';
        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteral(4)';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->setTarget(Target::TARGET_PROPERTY);
        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage @Enum supports only scalar values "array" given.
     */
    public function testAnnotationEnumInvalidTypeDeclarationException()
    {
        $parser     = $this->createTestParser();
        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumInvalid("foo")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->parse($docblock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Undefined enumerator value "3" for literal "AnnotationEnumLiteral::THREE".
     */
    public function testAnnotationEnumInvalidLiteralDeclarationException()
    {
        $parser     = $this->createTestParser();
        $docblock   = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteralInvalid("foo")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->parse($docblock);
    }

    public function getConstantsProvider()
    {
        $provider[] = [
            '@AnnotationWithConstants(PHP_EOL)',
            PHP_EOL
        ];
        $provider[] = [
            '@AnnotationWithConstants(AnnotationWithConstants::INTEGER)',
            AnnotationWithConstants::INTEGER
        ];
        $provider[] = [
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants(AnnotationWithConstants::STRING)',
            AnnotationWithConstants::STRING
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::FLOAT)',
            AnnotationWithConstants::FLOAT
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_)',
            ClassWithConstants::OTHER_KEY_
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_2)',
            ClassWithConstants::OTHER_KEY_2
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE
        ];
        $provider[] = [
            '@AnnotationWithConstants(InterfaceWithConstants::SOME_VALUE)',
            InterfaceWithConstants::SOME_VALUE
        ];
        $provider[] = [
            '@AnnotationWithConstants(\Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_VALUE)',
            InterfaceWithConstants::SOME_VALUE
        ];
        $provider[] = [
            '@AnnotationWithConstants({AnnotationWithConstants::STRING, AnnotationWithConstants::INTEGER, AnnotationWithConstants::FLOAT})',
            [AnnotationWithConstants::STRING, AnnotationWithConstants::INTEGER, AnnotationWithConstants::FLOAT]
        ];
        $provider[] = [
            '@AnnotationWithConstants({
                AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER
             })',
            [AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER]
        ];
        $provider[] = [
            '@AnnotationWithConstants({
                Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
             })',
            [InterfaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER]
        ];
        $provider[] = [
            '@AnnotationWithConstants({
                \Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
             })',
            [InterfaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER]
        ];
        $provider[] = [
            '@AnnotationWithConstants({
                AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER,
                ClassWithConstants::SOME_KEY = ClassWithConstants::SOME_VALUE,
                Doctrine\Tests\Common\Annotations\Fixtures\ClassWithConstants::SOME_KEY = InterfaceWithConstants::SOME_VALUE
             })',
            [
                AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER,
                ClassWithConstants::SOME_KEY    => ClassWithConstants::SOME_VALUE,
                ClassWithConstants::SOME_KEY    => InterfaceWithConstants::SOME_VALUE
            ]
        ];
        $provider[] = [
            '@AnnotationWithConstants(AnnotationWithConstants::class)',
            AnnotationWithConstants::class
        ];
        $provider[] = [
            '@AnnotationWithConstants({AnnotationWithConstants::class = AnnotationWithConstants::class})',
            [AnnotationWithConstants::class => AnnotationWithConstants::class]
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::class)',
            AnnotationWithConstants::class
        ];
        $provider[] = [
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::class)',
            AnnotationWithConstants::class
        ];
        return array_combine(array_column($provider, 0), $provider);
    }

    /**
     * @dataProvider getConstantsProvider
     */
    public function testSupportClassConstants($docblock, $expected)
    {
        $parser = $this->createTestParser();
        $parser->setImports([
            'classwithconstants'        => ClassWithConstants::class,
            'interfacewithconstants'    => InterfaceWithConstants::class,
            'annotationwithconstants'   => AnnotationWithConstants::class
        ]);

        $result = $parser->parse($docblock);
        self::assertInstanceOf(AnnotationWithConstants::class, $annotation = $result[0]);
        self::assertEquals($expected, $annotation->value);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation @SomeAnnotationClassNameWithoutConstructorAndProperties declared on  does not accept any values, but got {"value":"Foo"}.
     */
    public function testWithoutConstructorWhenIsNotDefaultValue()
    {
        $parser     = $this->createTestParser();
        $docblock   = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties("Foo")
 */
DOCBLOCK;


        $parser->setTarget(Target::TARGET_CLASS);
        $parser->parse($docblock);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation @SomeAnnotationClassNameWithoutConstructorAndProperties declared on  does not accept any values, but got {"value":"Foo"}.
     */
    public function testWithoutConstructorWhenHasNoProperties()
    {
        $parser     = $this->createTestParser();
        $docblock   = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties(value = "Foo")
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $parser->parse($docblock);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testAnnotationTargetSyntaxError()
    {
        $parser     = $this->createTestParser();
        $context    = 'class ' . 'SomeClassName';
        $docblock   = <<<DOCBLOCK
/**
 * @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid Target "Foo". Available targets: [ALL, CLASS, METHOD, PROPERTY, ANNOTATION]
     */
    public function testAnnotationWithInvalidTargetDeclarationError()
    {
        $parser     = $this->createTestParser();
        $context    = 'class ' . 'SomeClassName';
        $docblock   = <<<DOCBLOCK
/**
 * @AnnotationWithInvalidTargetDeclaration()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $parser->parse($docblock, $context);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage @Target expects either a string value, or an array of strings, "NULL" given.
     */
    public function testAnnotationWithTargetEmptyError()
    {
        $parser     = $this->createTestParser();
        $context    = 'class ' . 'SomeClassName';
        $docblock   = <<<DOCBLOCK
/**
 * @AnnotationWithTargetEmpty()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $parser->parse($docblock, $context);
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

        self::assertInstanceOf(Name::class, $result[0]);

        $docblock = <<<DOCBLOCK
/**
 * @Name
 * @Marker
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock);

        self::assertInstanceOf(Name::class, $result[0]);
    }

    /**
     * @group DDC-77
     */
    public function testAnnotationWithoutClassIsIgnoredWithoutWarning()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $result = $parser->parse('@param');

        self::assertEmpty($result);
    }

    /**
     * Tests if it's possible to ignore whole namespaces
     *
     * @param string $ignoreAnnotationName annotation/namespace to ignore
     * @param string $input                annotation/namespace from the docblock
     *
     * @return void
     *
     * @dataProvider provideTestIgnoreWholeNamespaces
     * @group 45
     */
    public function testIgnoreWholeNamespaces($ignoreAnnotationName, $input)
    {
        $parser = new DocParser();
        $parser->setIgnoredAnnotationNamespaces([$ignoreAnnotationName => true]);
        $result = $parser->parse($input);

        self::assertEmpty($result);
    }

    public function provideTestIgnoreWholeNamespaces()
    {
        return [
            ['Namespace', '@Namespace'],
            ['Namespace\\', '@Namespace'],

            ['Namespace', '@Namespace\Subnamespace'],
            ['Namespace\\', '@Namespace\Subnamespace'],

            ['Namespace', '@Namespace\Subnamespace\SubSubNamespace'],
            ['Namespace\\', '@Namespace\Subnamespace\SubSubNamespace'],

            ['Namespace\Subnamespace', '@Namespace\Subnamespace'],
            ['Namespace\Subnamespace\\', '@Namespace\Subnamespace'],

            ['Namespace\Subnamespace', '@Namespace\Subnamespace\SubSubNamespace'],
            ['Namespace\Subnamespace\\', '@Namespace\Subnamespace\SubSubNamespace'],

            ['Namespace\Subnamespace\SubSubNamespace', '@Namespace\Subnamespace\SubSubNamespace'],
            ['Namespace\Subnamespace\SubSubNamespace\\', '@Namespace\Subnamespace\SubSubNamespace'],
        ];
    }

    /**
     * @group DCOM-168
     */
    public function testNotAnAnnotationClassIsIgnoredWithoutWarning()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $parser->setIgnoredAnnotationNames([\PHPUnit\Framework\TestCase::class => true]);
        $result = $parser->parse('@\PHPUnit\Framework\TestCase');

        self::assertEmpty($result);
    }

    public function testNotAnAnnotationClassIsIgnoredWithoutWarningWithoutCheating()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $result = $parser->parse('@\PHPUnit\Framework\TestCase');

        self::assertEmpty($result);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
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

        self::assertEmpty($result);
    }

    /**
     * @group DCOM-41
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function testAnnotationThrowsExceptionWhenAtSignIsNotFollowedByIdentifierInNestedAnnotation()
    {
        $parser = new DocParser();
        $parser->parse("@Doctrine\Tests\Common\Annotations\Name(@')");
    }

    /**
     * @group DCOM-56
     */
    public function testAutoloadAnnotation()
    {
        self::assertFalse(class_exists('Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload', false), 'Pre-condition: Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload not allowed to be loaded.');

        $parser = new DocParser();

        AnnotationRegistry::registerAutoloadNamespace('Doctrine\Tests\Common\Annotations\Fixtures\Annotation', __DIR__ . '/../../../../');

        $parser->setImports([
            'autoload' => Fixtures\Annotation\Autoload::class,
        ]);
        $annotations = $parser->parse('@Autoload');

        self::assertCount(1, $annotations);
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function createTestParser()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $parser->setImports([
            'name' => Name::class,
            '__NAMESPACE__' => 'Doctrine\Tests\Common\Annotations',
        ]);

        return $parser;
    }

    /**
     * @group DDC-78
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
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
            self::assertEmpty($parser->parse($docblock));
        } catch (AnnotationException $e) {
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
            self::assertEmpty($parser->parse($docblock));
        } catch (AnnotationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @group DCOM-38
     */
    public function testCastInt()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=1234)');
        $annot = $result[0];
        self::assertInternalType('int', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeInt()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=-1234)');
        $annot = $result[0];
        self::assertInternalType('int', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastFloat()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=1234.345)');
        $annot = $result[0];
        self::assertInternalType('float', $annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeFloat()
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=-1234.345)');
        $annot = $result[0];
        self::assertInternalType('float', $annot->foo);

        $result = $parser->parse('@Marker(-1234.345)');
        $annot = $result[0];
        self::assertInternalType('float', $annot->value);
    }

     /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Creation Error] The annotation @SomeAnnotationClassNameWithoutConstructor declared on some class does not have a property named "invalidaProperty". Available properties: data, name
     */
    public function testSetValuesExeption()
    {
        $docblock = <<<DOCBLOCK
/**
 * @SomeAnnotationClassNameWithoutConstructor(invalidaProperty = "Some val")
 */
DOCBLOCK;

        $this->createTestParser()->parse($docblock, 'some class');
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Syntax Error] Expected Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER or Doctrine\Common\Annotations\DocLexer::T_TRUE or Doctrine\Common\Annotations\DocLexer::T_FALSE or Doctrine\Common\Annotations\DocLexer::T_NULL, got '3.42' at position 5.
     */
    public function testInvalidIdentifierInAnnotation()
    {
        $parser = $this->createTestParser();
        $parser->parse('@Foo\3.42');
    }

    public function testTrailingCommaIsAllowed()
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({
            "Foo",
            "Bar",
        })');
        self::assertCount(1, $annots);
        self::assertEquals(['Foo', 'Bar'], $annots[0]->value);
    }

    public function testTabPrefixIsAllowed()
    {
        $docblock = <<<DOCBLOCK
/**
 *	@Name
 */
DOCBLOCK;

        $parser = $this->createTestParser();
        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        self::assertInstanceOf(Name::class, $result[0]);
    }

    public function testDefaultAnnotationValueIsNotOverwritten()
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotWithDefaultValue');
        self::assertCount(1, $annots);
        self::assertEquals('bar', $annots[0]->foo);
    }

    public function testArrayWithColon()
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({"foo": "bar"})');
        self::assertCount(1, $annots);
        self::assertEquals(['foo' => 'bar'], $annots[0]->value);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Couldn't find constant foo.
     */
    public function testInvalidContantName()
    {
        $parser = $this->createTestParser();
        $parser->parse('@Name(foo: "bar")');
    }

    /**
     * Tests parsing empty arrays.
     */
    public function testEmptyArray()
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({"foo": {}})');
        self::assertCount(1, $annots);
        self::assertEquals(['foo' => []], $annots[0]->value);
    }

    public function testKeyHasNumber()
    {
        $parser = $this->createTestParser();
        $annots = $parser->parse('@SettingsAnnotation(foo="test", bar2="test")');

        self::assertCount(1, $annots);
        self::assertEquals(['foo' => 'test', 'bar2' => 'test'], $annots[0]->settings);
    }

    /**
     * @group 44
     */
    public function testSupportsEscapedQuotedValues()
    {
        $result = $this->createTestParser()->parse('@Doctrine\Tests\Common\Annotations\Name(foo="""bar""")');

        self::assertCount(1, $result);

        self::assertInstanceOf(Name::class, $result[0]);
        self::assertEquals('"bar"', $result[0]->foo);
    }

    /**
     * @see http://php.net/manual/en/mbstring.configuration.php
     * mbstring.func_overload can be changed only in php.ini
     * so for testing this case instead of skipping it you need to manually configure your php installation
     */
    public function testMultiByteAnnotation()
    {
        $overloadStringFunctions = 2;
        if (!extension_loaded('mbstring') || (ini_get('mbstring.func_overload') & $overloadStringFunctions) == 0) {
            $this->markTestSkipped('This test requires mbstring function overloading is turned on');
        }

        $docblock = <<<DOCBLOCK
        /**
         * Мультибайтовый текст ломал парсер при оверлоадинге строковых функций
         * @Doctrine\Tests\Common\Annotations\Name
         */
DOCBLOCK;

        $docParser = $this->createTestParser();
        $result = $docParser->parse($docblock);

        self::assertCount(1, $result);

    }

    public function testWillNotParseAnnotationSucceededByAnImmediateDash()
    {
        $parser = $this->createTestParser();

        self::assertEmpty($parser->parse('@SomeAnnotationClassNameWithoutConstructorAndProperties-'));
    }

    public function testWillParseAnnotationSucceededByANonImmediateDash()
    {
        $result = $this
            ->createTestParser()
            ->parse('@SomeAnnotationClassNameWithoutConstructorAndProperties -');

        self::assertCount(1, $result);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructorAndProperties::class, $result[0]);
    }
}

/** @Annotation */
class SettingsAnnotation
{
    public $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }
}

/** @Annotation */
class SomeAnnotationClassNameWithoutConstructor
{
    public $data;
    public $name;
}

/** @Annotation */
class SomeAnnotationWithConstructorWithoutParams
{
    public function __construct()
    {
        $this->data = 'Some data';
    }
    public $data;
    public $name;
}

/** @Annotation */
class SomeAnnotationClassNameWithoutConstructorAndProperties{}

/**
 * @Annotation
 * @Target("Foo")
 */
class AnnotationWithInvalidTargetDeclaration{}

/**
 * @Annotation
 * @Target
 */
class AnnotationWithTargetEmpty{}

/** @Annotation */
class AnnotationExtendsAnnotationTargetAll extends AnnotationTargetAll
{
}

/** @Annotation */
class Name extends Annotation {
    public $foo;
}

/** @Annotation */
class Marker {
    public $value;
}

namespace Doctrine\Tests\Common\Annotations\FooBar;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
class Name extends Annotation {
}
