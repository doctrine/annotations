<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithConstants;
use Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants;
use InvalidArgumentException;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;

use function array_column;
use function array_combine;
use function assert;
use function class_exists;
use function extension_loaded;
use function get_parent_class;
use function ini_get;
use function method_exists;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

class DocParserTest extends TestCase
{
    public function testNestedArraysWithNestedAnnotation(): void
    {
        $parser = $this->createTestParser();

        // Nested arrays with nested annotations
        $result = $parser->parse('@Name(foo={1,2, {"key"=@Name}})');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertNull($annot->value);
        self::assertCount(3, $annot->foo);
        self::assertEquals(1, $annot->foo[0]);
        self::assertEquals(2, $annot->foo[1]);
        self::assertIsArray($annot->foo[2]);

        $nestedArray = $annot->foo[2];
        self::assertTrue(isset($nestedArray['key']));
        self::assertInstanceOf(Name::class, $nestedArray['key']);
    }

    public function testBasicAnnotations(): void
    {
        $parser = $this->createTestParser();

        // Marker annotation
        $result = $parser->parse('@Name');
        $annot  = $result[0];
        self::assertInstanceOf(Name::class, $annot);
        self::assertNull($annot->value);
        self::assertNull($annot->foo);

        // Associative arrays
        $result = $parser->parse('@Name(foo={"key1" = "value1"})');
        $annot  = $result[0];
        self::assertNull($annot->value);
        self::assertIsArray($annot->foo);
        self::assertTrue(isset($annot->foo['key1']));

        // Numerical arrays
        $result = $parser->parse('@Name({2="foo", 4="bar"})');
        $annot  = $result[0];
        self::assertIsArray($annot->value);
        self::assertEquals('foo', $annot->value[2]);
        self::assertEquals('bar', $annot->value[4]);
        self::assertFalse(isset($annot->value[0]));
        self::assertFalse(isset($annot->value[1]));
        self::assertFalse(isset($annot->value[3]));

        // Multiple values
        $result = $parser->parse('@Name(@Name, @Name)');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertIsArray($annot->value);
        self::assertInstanceOf(Name::class, $annot->value[0]);
        self::assertInstanceOf(Name::class, $annot->value[1]);

        // Positionals arguments following named arguments
        $result = $parser->parse('@Name(foo="bar", @Name)');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertEquals('bar', $annot->foo);
        self::assertInstanceOf(Name::class, $annot->value);

        // Multiple positionals arguments following named arguments
        $result = $parser->parse('@Name(@Name, foo="baz", @Name)');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertEquals('baz', $annot->foo);
        self::assertIsArray($annot->value);
        self::assertInstanceOf(Name::class, $annot->value[0]);
        self::assertInstanceOf(Name::class, $annot->value[1]);

        // Multiple scalar values
        $result = $parser->parse('@Name("foo", "bar")');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertIsArray($annot->value);
        self::assertEquals('foo', $annot->value[0]);
        self::assertEquals('bar', $annot->value[1]);

        // Multiple types as values
        $result = $parser->parse('@Name(foo="Bar", @Name, {"key1"="value1", "key2"="value2"})');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertIsArray($annot->value);
        self::assertInstanceOf(Name::class, $annot->value[0]);
        self::assertIsArray($annot->value[1]);
        self::assertEquals('value1', $annot->value[1]['key1']);
        self::assertEquals('value2', $annot->value[1]['key2']);

        // Complete docblock
        $docblock = <<<'DOCBLOCK'
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

    public function testDefaultValueAnnotations(): void
    {
        $parser = $this->createTestParser();

        // Array as first value
        $result = $parser->parse('@Name({"key1"="value1"})');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertIsArray($annot->value);
        self::assertEquals('value1', $annot->value['key1']);

        // Array as first value and additional values
        $result = $parser->parse('@Name({"key1"="value1"}, foo="bar")');
        $annot  = $result[0];

        self::assertInstanceOf(Name::class, $annot);
        self::assertIsArray($annot->value);
        self::assertEquals('value1', $annot->value['key1']);
        self::assertEquals('bar', $annot->foo);
    }

    public function testNamespacedAnnotations(): void
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $docblock = <<<'DOCBLOCK'
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
    public function testTypicalMethodDocBlock(): void
    {
        $parser = $this->createTestParser();

        $docblock = <<<'DOCBLOCK'
/**
 * Some nifty method.
 *
 * @since 2.0
 * @Doctrine\Tests\Common\Annotations\Name(foo="bar")
 * @param string $foo This is foo.
 * @param mixed $bar This is bar.
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

    public function testAnnotationWithoutConstructor(): void
    {
        $parser = $this->createTestParser();

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructor::class, $annot);

        self::assertNull($annot->name);
        self::assertNotNull($annot->data);
        self::assertEquals($annot->data, 'Some data');

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor(name="Some Name", data = "Some data")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertNotNull($annot);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructor::class, $annot);

        self::assertEquals($annot->name, 'Some Name');
        self::assertEquals($annot->data, 'Some data');

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor(data = "Some data")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertEquals($annot->data, 'Some data');
        self::assertNull($annot->name);

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor(name = "Some name")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertNull($annot->data);

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertEquals($annot->data, 'Some data');
        self::assertNull($annot->name);

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor("Some data",name = "Some name")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertEquals($annot->data, 'Some data');

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithoutParams(name = "Some name")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertEquals($annot->name, 'Some name');
        self::assertEquals($annot->data, 'Some data');

        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties()
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructorAndProperties::class, $result[0]);
    }

    public function testAnnotationTarget(): void
    {
        $parser = new DocParser();
        $parser->setImports(['__NAMESPACE__' => 'Doctrine\Tests\Common\Annotations\Fixtures']);
        $class = new ReflectionClass(Fixtures\ClassWithValidAnnotationTarget::class);

        $context    = 'class ' . $class->getName();
        $docComment = $class->getDocComment();

        $parser->setTarget(Target::TARGET_CLASS);
        self::assertNotNull($parser->parse($docComment, $context));

        $property   = $class->getProperty('foo');
        $docComment = $property->getDocComment();
        $context    = 'property ' . $class->getName() . '::$' . $property->getName();

        $parser->setTarget(Target::TARGET_PROPERTY);
        self::assertNotNull($parser->parse($docComment, $context));

        $method     = $class->getMethod('someFunction');
        $docComment = $property->getDocComment();
        $context    = 'method ' . $class->getName() . '::' . $method->getName() . '()';

        $parser->setTarget(Target::TARGET_METHOD);
        self::assertNotNull($parser->parse($docComment, $context));

        try {
            $class      = new ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtClass::class);
            $context    = 'class ' . $class->getName();
            $docComment = $class->getDocComment();

            $parser->setTarget(Target::TARGET_CLASS);
            $parser->parse($docComment, $context);

            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertNotNull($exc->getMessage());
        }

        try {
            $class      = new ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtMethod::class);
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
            $class      = new ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class);
            $property   = $class->getProperty('foo');
            $docComment = $property->getDocComment();
            $context    = 'property ' . $class->getName() . '::$' . $property->getName();

            $parser->setTarget(Target::TARGET_PROPERTY);
            $parser->parse($docComment, $context);

            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertNotNull($exc->getMessage());
        }
    }

    /**
     * @phpstan-return list<array{string, string}>
     */
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
             [
                 'arrayOfAnnotations',
                 '{
                     @AnnotationExtendsAnnotationTargetAll,
                     @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll
                 }',
             ],

            // annotation instance
             ['annotation', '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll'],
             ['annotation', '@AnnotationExtendsAnnotationTargetAll'],
         ];
    }

    /**
     * @phpstan-return list<array{string, string, string, string}>
     */
    public function getAnnotationVarTypeProviderInvalid(): array
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
             [
                 'annotation',
                 AnnotationTargetAll::class,
                 '@Name',
                 'an instance of Doctrine\Tests\Common\Annotations\Name',
             ],
         ];
    }

    /**
     * @phpstan-return list<array{string, string, string, string}>
     */
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
             [
                 'arrayOfAnnotations',
                 AnnotationTargetAll::class,
                 '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,true}',
                 'boolean',
             ],
             [
                 'arrayOfAnnotations',
                 AnnotationTargetAll::class,
                 '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,true}',
                 'boolean',
             ],
             [
                 'arrayOfAnnotations',
                 AnnotationTargetAll::class,
                 '{@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,1.2}',
                 'double',
             ],
             [
                 'arrayOfAnnotations',
                 AnnotationTargetAll::class,
                 '{
                     @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll,
                     @AnnotationExtendsAnnotationTargetAll,
                     "str"
                 }',
                 'string',
             ],
         ];
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderValid
     */
    public function testAnnotationWithVarType(string $attribute, string $value): void
    {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::$invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        $result = $parser->parse($docblock, $context);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithVarType::class, $result[0]);
        self::assertNotNull($result[0]->$attribute);
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderInvalid
     */
    public function testAnnotationWithVarTypeError(
        string $attribute,
        string $type,
        string $value,
        string $given
    ): void {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringMatchesFormat(
                '[Type Error] Attribute "' . $attribute .
                '" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType' .
                ' declared on property SomeClassName::invalidProperty. expects a(n) %A' .
                $type . ', but got ' . $given . '.',
                $exc->getMessage()
            );
        }
    }

    /**
     * @dataProvider getAnnotationVarTypeArrayProviderInvalid
     */
    public function testAnnotationWithVarTypeArrayError(
        string $attribute,
        string $type,
        string $value,
        string $given
    ): void {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringMatchesFormat(
                '[Type Error] Attribute "' . $attribute .
                '" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithVarType' .
                ' declared on property SomeClassName::invalidProperty. expects either a(n) %A' .
                $type . ', or an array of %A' . $type . 's, but got ' . $given . '.',
                $exc->getMessage()
            );
        }
    }

    /**
     * @dataProvider getAnnotationVarTypeProviderValid
     */
    public function testAnnotationWithAttributes(string $attribute, string $value): void
    {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::$invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        $result = $parser->parse($docblock, $context);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithAttributes::class, $result[0]);
        $getter = 'get' . ucfirst($attribute);
        self::assertNotNull($result[0]->$getter());
    }

   /**
    * @dataProvider getAnnotationVarTypeProviderInvalid
    */
    public function testAnnotationWithAttributesError(
        string $attribute,
        string $type,
        string $value,
        string $given
    ): void {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(sprintf(
                '[Type Error] Attribute "%s" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes' .
                ' declared on property SomeClassName::invalidProperty. expects a(n) %s, but got %s.',
                $attribute,
                $type,
                $given
            ), $exc->getMessage());
        }
    }

   /**
    * @dataProvider getAnnotationVarTypeArrayProviderInvalid
    */
    public function testAnnotationWithAttributesWithVarTypeArrayError(
        string $attribute,
        string $type,
        string $value,
        string $given
    ): void {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = sprintf(
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes(%s = %s)',
            $attribute,
            $value
        );
        $parser->setTarget(Target::TARGET_PROPERTY);

        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(sprintf(
                '[Type Error] Attribute "%s" of' .
                ' @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithAttributes declared' .
                ' on property SomeClassName::invalidProperty. expects either a(n) %s, or an array of %ss, but got %s.',
                $attribute,
                $type,
                $type,
                $given
            ), $exc->getMessage());
        }
    }

    public function testAnnotationWithRequiredAttributes(): void
    {
        $parser  = $this->createTestParser();
        $context = 'property SomeClassName::invalidProperty.';
        $parser->setTarget(Target::TARGET_PROPERTY);

        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes' .
            '("Some Value", annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        $result   = $parser->parse($docblock);

        self::assertCount(1, $result);

        $annotation = $result[0];
        assert($annotation instanceof Fixtures\AnnotationWithRequiredAttributes);

        self::assertInstanceOf(Fixtures\AnnotationWithRequiredAttributes::class, $annotation);
        self::assertEquals('Some Value', $annotation->getValue());
        self::assertInstanceOf(Fixtures\AnnotationTargetAnnotation::class, $annotation->getAnnot());

        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes("Some Value")';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(
                'Attribute "annot" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes' .
                ' declared on property SomeClassName::invalidProperty. expects a(n)' .
                ' Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation.' .
                ' This value should not be null.',
                $exc->getMessage()
            );
        }

        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes' .
            '(annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(
                'Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributes' .
                ' declared on property SomeClassName::invalidProperty. expects a(n) string.' .
                ' This value should not be null.',
                $exc->getMessage()
            );
        }
    }

    public function testAnnotationWithRequiredAttributesWithoutConstructor(): void
    {
        $parser  = $this->createTestParser();
        $context = 'property SomeClassName::invalidProperty.';
        $parser->setTarget(Target::TARGET_PROPERTY);

        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor' .
            '("Some Value", annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        $result   = $parser->parse($docblock);

        self::assertCount(1, $result);
        self::assertInstanceOf(Fixtures\AnnotationWithRequiredAttributesWithoutConstructor::class, $result[0]);
        self::assertEquals('Some Value', $result[0]->value);
        self::assertInstanceOf(Fixtures\AnnotationTargetAnnotation::class, $result[0]->annot);

        $docblock = <<<'ANNOTATION'
@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor("Some Value")
ANNOTATION;
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(
                'Attribute "annot" of' .
                ' @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor' .
                ' declared on property SomeClassName::invalidProperty. expects a(n)' .
                ' \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation.' .
                ' This value should not be null.',
                $exc->getMessage()
            );
        }

        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor' .
        '(annot = @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation)';
        try {
            $parser->parse($docblock, $context);
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertStringContainsString(
                'Attribute "value" of' .
                ' @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithRequiredAttributesWithoutConstructor' .
                ' declared on property SomeClassName::invalidProperty. expects a(n) string.' .
                ' This value should not be null.',
                $exc->getMessage()
            );
        }
    }

    public function testAnnotationEnumeratorException(): void
    {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum("FOUR")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->setTarget(Target::TARGET_PROPERTY);
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            'Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum declared' .
            ' on property SomeClassName::invalidProperty. accepts only [ONE, TWO, THREE], but got FOUR.'
        );
        $parser->parse($docblock, $context);
    }

    public function testAnnotationEnumeratorLiteralException(): void
    {
        $parser   = $this->createTestParser();
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteral(4)';

        $parser->setIgnoreNotImportedAnnotations(false);
        $parser->setTarget(Target::TARGET_PROPERTY);
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            'Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteral declared' .
            ' on property SomeClassName::invalidProperty. accepts only' .
            ' [AnnotationEnumLiteral::ONE, AnnotationEnumLiteral::TWO, AnnotationEnumLiteral::THREE], but got 4.'
        );
        $parser->parse($docblock, $context);
    }

    public function testAnnotationEnumInvalidTypeDeclarationException(): void
    {
        $parser   = $this->createTestParser();
        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumInvalid("foo")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $this->expectException(AnnotationException::class);
        try {
            $parser->parse($docblock);
        } catch (AnnotationException $exc) {
            $previous = $exc->getPrevious();
            $this->assertInstanceOf(InvalidArgumentException::class, $previous);
            $this->assertThat($previous, new ExceptionMessage('@Enum supports only scalar values "array" given.'));

            throw $exc;
        }
    }

    public function testAnnotationEnumInvalidLiteralDeclarationException(): void
    {
        $parser   = $this->createTestParser();
        $docblock = '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnumLiteralInvalid("foo")';

        $parser->setIgnoreNotImportedAnnotations(false);
        $this->expectException(AnnotationException::class);
        try {
            $parser->parse($docblock);
        } catch (AnnotationException $exc) {
            $previous = $exc->getPrevious();
            $this->assertInstanceOf(InvalidArgumentException::class, $previous);
            $this->assertThat(
                $previous,
                new ExceptionMessage('Undefined enumerator value "3" for literal "AnnotationEnumLiteral::THREE".')
            );

            throw $exc;
        }
    }

    /**
     * @phpstan-return array<string, array{string, mixed}>
     */
    public function getConstantsProvider(): array
    {
        $provider   = [];
        $provider[] = [
            '@AnnotationWithConstants(PHP_EOL)',
            PHP_EOL,
        ];
        $provider[] = [
            '@AnnotationWithConstants(AnnotationWithConstants::INTEGER)',
            AnnotationWithConstants::INTEGER,
        ];
        $provider[] = [
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants(AnnotationWithConstants::STRING)',
            AnnotationWithConstants::STRING,
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::FLOAT)',
            AnnotationWithConstants::FLOAT,
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE,
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_)',
            ClassWithConstants::OTHER_KEY_,
        ];
        $provider[] = [
            '@AnnotationWithConstants(ClassWithConstants::OTHER_KEY_2)',
            ClassWithConstants::OTHER_KEY_2,
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\ClassWithConstants::SOME_VALUE)',
            ClassWithConstants::SOME_VALUE,
        ];
        $provider[] = [
            '@AnnotationWithConstants(InterfaceWithConstants::SOME_VALUE)',
            InterfaceWithConstants::SOME_VALUE,
        ];
        $provider[] = [
            '@AnnotationWithConstants(\Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_VALUE)',
            InterfaceWithConstants::SOME_VALUE,
        ];
        $provider[] = [<<<'ANNOTATION'
@AnnotationWithConstants({
    AnnotationWithConstants::STRING,
    AnnotationWithConstants::INTEGER,
    AnnotationWithConstants::FLOAT
})
ANNOTATION
,
            [AnnotationWithConstants::STRING, AnnotationWithConstants::INTEGER, AnnotationWithConstants::FLOAT],
        ];
        $provider[] = [
            '@AnnotationWithConstants({
                AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER
             })',
            [AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER],
        ];
        $provider[] = [<<<'ANNOTATION'
@AnnotationWithConstants({
    Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
})
ANNOTATION
,
            [InterfaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER],
        ];
        $provider[] = [<<<'ANNOTATION'
@AnnotationWithConstants({
    \Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_KEY = AnnotationWithConstants::INTEGER
})
ANNOTATION
,
            [InterfaceWithConstants::SOME_KEY => AnnotationWithConstants::INTEGER],
        ];
        $provider[] = [<<<'ANNOTATION'
@AnnotationWithConstants({
    AnnotationWithConstants::STRING = AnnotationWithConstants::INTEGER,
    ClassWithConstants::SOME_KEY = ClassWithConstants::SOME_VALUE,
    Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_KEY = InterfaceWithConstants::SOME_VALUE
})
ANNOTATION
,
            [
                AnnotationWithConstants::STRING => AnnotationWithConstants::INTEGER,
                ClassWithConstants::SOME_KEY    => ClassWithConstants::SOME_VALUE,
                InterfaceWithConstants::SOME_KEY    => InterfaceWithConstants::SOME_VALUE,
            ],
        ];
        $provider[] = [
            '@AnnotationWithConstants(AnnotationWithConstants::class)',
            AnnotationWithConstants::class,
        ];
        $provider[] = [
            '@AnnotationWithConstants({AnnotationWithConstants::class = AnnotationWithConstants::class})',
            [AnnotationWithConstants::class => AnnotationWithConstants::class],
        ];
        $provider[] = [
            '@AnnotationWithConstants(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::class)',
            AnnotationWithConstants::class,
        ];
        $provider[] = [
            '@Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants' .
            '(Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants::class)',
            AnnotationWithConstants::class,
        ];

        return array_combine(array_column($provider, 0), $provider);
    }

    /**
     * @param mixed $expected
     *
     * @dataProvider getConstantsProvider
     */
    public function testSupportClassConstants(string $docblock, $expected): void
    {
        $parser = $this->createTestParser();
        $parser->setImports([
            'classwithconstants'        => ClassWithConstants::class,
            'interfacewithconstants'    => InterfaceWithConstants::class,
            'annotationwithconstants'   => AnnotationWithConstants::class,
        ]);

        $result = $parser->parse($docblock);
        self::assertInstanceOf(AnnotationWithConstants::class, $annotation = $result[0]);
        self::assertEquals($expected, $annotation->value);
    }

    public function testWithoutConstructorWhenIsNotDefaultValue(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties("Foo")
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            'The annotation @SomeAnnotationClassNameWithoutConstructorAndProperties declared on ' .
            ' does not accept any values, but got {"value":"Foo"}.'
        );
        $parser->parse($docblock);
    }

    public function testWithoutConstructorWhenHasNoProperties(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructorAndProperties(value = "Foo")
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            'The annotation @SomeAnnotationClassNameWithoutConstructorAndProperties declared on ' .
            ' does not accept any values, but got {"value":"Foo"}.'
        );
        $parser->parse($docblock);
    }

    public function testAnnotationTargetSyntaxError(): void
    {
        $parser   = $this->createTestParser();
        $context  = 'class SomeClassName';
        $docblock = <<<'DOCBLOCK'
/**
 * @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            "Expected namespace separator or identifier, got ')' at position 24" .
            ' in class @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError.'
        );
        $parser->parse($docblock, $context);
    }

    public function testAnnotationWithInvalidTargetDeclarationError(): void
    {
        $parser   = $this->createTestParser();
        $context  = 'class SomeClassName';
        $docblock = <<<'DOCBLOCK'
/**
 * @AnnotationWithInvalidTargetDeclaration()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $this->expectException(AnnotationException::class);
        try {
            $parser->parse($docblock, $context);
        } catch (AnnotationException $exc) {
            $previous = $exc->getPrevious();
            $this->assertInstanceOf(InvalidArgumentException::class, $previous);
            $this->assertThat(
                $previous,
                new ExceptionMessage(
                    'Invalid Target "Foo". Available targets: [ALL, CLASS, METHOD, PROPERTY, FUNCTION, ANNOTATION]'
                )
            );

            throw $exc;
        }
    }

    public function testAnnotationWithTargetEmptyError(): void
    {
        $parser   = $this->createTestParser();
        $context  = 'class SomeClassName';
        $docblock = <<<'DOCBLOCK'
/**
 * @AnnotationWithTargetEmpty()
 */
DOCBLOCK;

        $parser->setTarget(Target::TARGET_CLASS);
        $this->expectException(AnnotationException::class);
        try {
            $parser->parse($docblock, $context);
        } catch (AnnotationException $exc) {
            $previous = $exc->getPrevious();
            $this->assertInstanceOf(InvalidArgumentException::class, $previous);
            $this->assertThat(
                $previous,
                new ExceptionMessage('@Target expects either a string value, or an array of strings, "NULL" given.')
            );

            throw $exc;
        }
    }

    /**
     * @group DDC-575
     */
    public function testRegressionDDC575(): void
    {
        $parser = $this->createTestParser();

        $docblock = <<<'DOCBLOCK'
/**
 * @Name
 *
 * Will trigger error.
 */
DOCBLOCK;

        $result = $parser->parse($docblock);

        self::assertInstanceOf(Name::class, $result[0]);

        $docblock = <<<'DOCBLOCK'
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
    public function testAnnotationWithoutClassIsIgnoredWithoutWarning(): void
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
     * @dataProvider provideTestIgnoreWholeNamespaces
     * @group 45
     */
    public function testIgnoreWholeNamespaces($ignoreAnnotationName, $input): void
    {
        $parser = new DocParser();
        $parser->setIgnoredAnnotationNamespaces([$ignoreAnnotationName => true]);
        $result = $parser->parse($input);

        self::assertEmpty($result);
    }

    /**
     * @phpstan-return list<array{string, string}>
     */
    public function provideTestIgnoreWholeNamespaces(): array
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
    public function testNotAnAnnotationClassIsIgnoredWithoutWarning(): void
    {
        $parser = new DocParser();
        $parser->setIgnoredAnnotationNames([TestCase::class => true]);
        $result = $parser->parse('@\PHPUnit\Framework\TestCase');

        self::assertEmpty($result);
    }

    public function testNotAnAnnotationClassIsIgnoredWithoutWarningWithoutCheating(): void
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $result = $parser->parse('@\PHPUnit\Framework\TestCase');

        self::assertEmpty($result);
    }

    public function testAnnotationDontAcceptSingleQuotes(): void
    {
        $parser = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage("Expected PlainValue, got ''' at position 10.");
        $parser->parse("@Name(foo='bar')");
    }

    /**
     * @group DCOM-41
     */
    public function testAnnotationDoesntThrowExceptionWhenAtSignIsNotFollowedByIdentifier(): void
    {
        $parser = new DocParser();
        $result = $parser->parse("'@'");

        self::assertEmpty($result);
    }

    /**
     * @group DCOM-41
     */
    public function testAnnotationThrowsExceptionWhenAtSignIsNotFollowedByIdentifierInNestedAnnotation(): void
    {
        $parser = new DocParser();
        $this->expectException(AnnotationException::class);
        $parser->parse("@Doctrine\Tests\Common\Annotations\Name(@')");
    }

    /**
     * @group DCOM-56
     */
    public function testAutoloadAnnotation(): void
    {
        self::assertFalse(
            class_exists('Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload', false),
            'Pre-condition: Doctrine\Tests\Common\Annotations\Fixture\Annotation\Autoload not allowed to be loaded.'
        );

        $parser = new DocParser();

        AnnotationRegistry::registerAutoloadNamespace(
            'Doctrine\Tests\Common\Annotations\Fixtures\Annotation',
            __DIR__ . '/../../../../'
        );

        $parser->setImports([
            'autoload' => Fixtures\Annotation\Autoload::class,
        ]);
        $annotations = $parser->parse('@Autoload');

        self::assertCount(1, $annotations);
        self::assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function createTestParser(): DocParser
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
     */
    public function testSyntaxErrorWithContextDescription(): void
    {
        $parser = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            "Expected PlainValue, got ''' at position 10 in class \Doctrine\Tests\Common\Annotations\Name"
        );
        $parser->parse("@Name(foo='bar')", 'class \Doctrine\Tests\Common\Annotations\Name');
    }

    /**
     * @group DDC-183
     */
    public function testSyntaxErrorWithUnknownCharacters(): void
    {
        $docblock = <<<'DOCBLOCK'
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
    public function testIgnorePHPDocThrowTag(): void
    {
        $docblock = <<<'DOCBLOCK'
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
    public function testCastInt(): void
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=1234)');
        $annot  = $result[0];
        self::assertIsInt($annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeInt(): void
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=-1234)');
        $annot  = $result[0];
        self::assertIsInt($annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastFloat(): void
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=1234.345)');
        $annot  = $result[0];
        self::assertIsFloat($annot->foo);
    }

    /**
     * @group DCOM-38
     */
    public function testCastNegativeFloat(): void
    {
        $parser = $this->createTestParser();

        $result = $parser->parse('@Name(foo=-1234.345)');
        $annot  = $result[0];
        self::assertIsFloat($annot->foo);

        $result = $parser->parse('@Marker(-1234.345)');
        $annot  = $result[0];
        self::assertIsFloat($annot->value);
    }

    public function testSetValuesException(): void
    {
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationClassNameWithoutConstructor(invalidaProperty = "Some val")
 */
DOCBLOCK;

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            '[Creation Error] The annotation @SomeAnnotationClassNameWithoutConstructor declared' .
            ' on some class does not have a property named "invalidaProperty".
Available properties: data, name'
        );
        $this->createTestParser()->parse($docblock, 'some class');
    }

    public function testInvalidIdentifierInAnnotation(): void
    {
        $parser = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('[Syntax Error] Expected Doctrine\Common\Annotations\DocLexer::T_IDENTIFIER' .
            ' or Doctrine\Common\Annotations\DocLexer::T_TRUE' .
            ' or Doctrine\Common\Annotations\DocLexer::T_FALSE' .
            " or Doctrine\Common\Annotations\DocLexer::T_NULL, got '3.42' at position 5.");
        $parser->parse('@Foo\3.42');
    }

    public function testTrailingCommaIsAllowed(): void
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({
            "Foo",
            "Bar",
        })');
        self::assertCount(1, $annots);
        self::assertEquals(['Foo', 'Bar'], $annots[0]->value);
    }

    public function testTabPrefixIsAllowed(): void
    {
        $docblock = <<<'DOCBLOCK'
/**
 *	@Name
 */
DOCBLOCK;

        $parser = $this->createTestParser();
        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        self::assertInstanceOf(Name::class, $result[0]);
    }

    public function testDefaultAnnotationValueIsNotOverwritten(): void
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotWithDefaultValue');
        self::assertCount(1, $annots);
        self::assertEquals('bar', $annots[0]->foo);
    }

    public function testArrayWithColon(): void
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({"foo": "bar"})');
        self::assertCount(1, $annots);
        self::assertEquals(['foo' => 'bar'], $annots[0]->value);
    }

    public function testInvalidContantName(): void
    {
        $parser = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage("[Semantical Error] Couldn't find constant foo.");
        $parser->parse('@Name(foo: "bar")');
    }

    /**
     * Tests parsing empty arrays.
     */
    public function testEmptyArray(): void
    {
        $parser = $this->createTestParser();

        $annots = $parser->parse('@Name({"foo": {}})');
        self::assertCount(1, $annots);
        self::assertEquals(['foo' => []], $annots[0]->value);
    }

    public function testKeyHasNumber(): void
    {
        $parser = $this->createTestParser();
        $annots = $parser->parse('@SettingsAnnotation(foo="test", bar2="test")');

        self::assertCount(1, $annots);
        self::assertEquals(['foo' => 'test', 'bar2' => 'test'], $annots[0]->settings);
    }

    /**
     * @group 44
     */
    public function testSupportsEscapedQuotedValues(): void
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
    public function testMultiByteAnnotation(): void
    {
        $overloadStringFunctions = 2;
        if (! extension_loaded('mbstring') || (ini_get('mbstring.func_overload') & $overloadStringFunctions) === 0) {
            $this->markTestSkipped('This test requires mbstring function overloading is turned on');
        }

        $docblock = <<<'DOCBLOCK'
        /**
         * Мультибайтовый текст ломал парсер при оверлоадинге строковых функций
         * @Doctrine\Tests\Common\Annotations\Name
         */
DOCBLOCK;

        $docParser = $this->createTestParser();
        $result    = $docParser->parse($docblock);

        self::assertCount(1, $result);
    }

    public function testWillNotParseAnnotationSucceededByAnImmediateDash(): void
    {
        $parser = $this->createTestParser();

        self::assertEmpty($parser->parse('@SomeAnnotationClassNameWithoutConstructorAndProperties-'));
    }

    public function testWillParseAnnotationSucceededByANonImmediateDash(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('@SomeAnnotationClassNameWithoutConstructorAndProperties -');

        self::assertCount(1, $result);
        self::assertInstanceOf(SomeAnnotationClassNameWithoutConstructorAndProperties::class, $result[0]);
    }

    public function testNamedArgumentsConstructorInterface(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @NamedAnnotation(foo="baz", bar=2222) */');

        self::assertCount(1, $result);
        self::assertInstanceOf(NamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(2222, $result[0]->getBar());
    }

    public function testNamedReorderedArgumentsConstructorInterface(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @NamedAnnotation(bar=2222, foo="baz") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(NamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(2222, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorInterfaceWithDefaultValue(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @NamedAnnotation(foo="baz") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(NamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(1234, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorInterfaceWithExtraArguments(): void
    {
        $docParser = $this->createTestParser();

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "invalid"\s.*\sAvailable named arguments: foo, bar/'
        );

        $docParser->parse('/** @NamedAnnotation(foo="baz", invalid="uh oh") */');
    }

    public function testNamedArgumentsConstructorAnnotation(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @AnotherNamedAnnotation(foo="baz", bar=2222) */');

        self::assertCount(1, $result);
        self::assertInstanceOf(AnotherNamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(2222, $result[0]->getBar());
    }

    public function testNamedReorderedArgumentsConstructorAnnotation(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @AnotherNamedAnnotation(bar=2222, foo="baz") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(AnotherNamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(2222, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorAnnotationWithDefaultValue(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @AnotherNamedAnnotation(foo="baz") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(AnotherNamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(1234, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorAnnotationWithDefaultProperty(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @AnotherNamedAnnotation("baz") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(AnotherNamedAnnotation::class, $result[0]);
        self::assertSame('baz', $result[0]->getFoo());
        self::assertSame(1234, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorAnnotationWithExtraArguments(): void
    {
        $docParser = $this->createTestParser();

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "invalid"\s.*\sAvailable named arguments: foo, bar/'
        );

        $docParser->parse('/** @AnotherNamedAnnotation(foo="baz", invalid="uh oh") */');
    }

    public function testNamedArgumentsConstructorAnnotationWithDefaultPropertyAsArray(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @NamedAnnotationWithArray({"foo","bar","baz"},bar=567) */');

        self::assertCount(1, $result);
        self::assertInstanceOf(NamedAnnotationWithArray::class, $result[0]);
        self::assertSame(['foo', 'bar', 'baz'], $result[0]->getFoo());
        self::assertSame(567, $result[0]->getBar());
    }

    public function testNamedArgumentsConstructorAnnotationWithDefaultPropertySet(): void
    {
        $result = $this
            ->createTestParser()
            ->parse('/** @AnotherNamedAnnotation("baz", foo="bar") */');

        self::assertCount(1, $result);
        self::assertInstanceOf(AnotherNamedAnnotation::class, $result[0]);
        self::assertSame('bar', $result[0]->getFoo());
    }

    public function testNamedArgumentsConstructorAnnotationWithInvalidArguments(): void
    {
        $parser = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            '[Syntax Error] Expected Positional arguments after named arguments is not allowed'
        );
        $parser->parse('/** @AnotherNamedAnnotation("foo", bar=666, "hey") */');
    }

    public function testNamedArgumentsConstructorAnnotationWithWrongArgumentType(): void
    {
        $context  = 'property SomeClassName::invalidProperty.';
        $docblock = '@NamedAnnotationWithArray(foo = "no array!")';
        $parser   = $this->createTestParser();
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/\[Creation Error\] An error occurred while instantiating the annotation '
            . '@NamedAnnotationWithArray declared on property SomeClassName::invalidProperty\.: ".*"\.$/'
        );
        try {
            $parser->parse($docblock, $context);
        } catch (AnnotationException $exc) {
            $this->assertInstanceOf(TypeError::class, $exc->getPrevious());

            throw $exc;
        }
    }

    public function testAnnotationWithConstructorWithVariadicParamAndExtraNamedArguments(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam(name = "Some data", foo = "Foo", bar = "Bar")
 */
DOCBLOCK;

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "foo"\s.*\sAvailable named arguments: name/'
        );

        $parser->parse($docblock);
    }

    public function testAnnotationWithConstructorWithVariadicParamAndExtraNamedArgumentsShuffled(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam(foo = "Foo", name = "Some data", bar = "Bar")
 */
DOCBLOCK;

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "foo"\s.*\sAvailable named arguments: name/'
        );

        $parser->parse($docblock);
    }

    public function testAnnotationWithConstructorWithVariadicParamAndCombinedNamedAndPositionalArguments(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam("Some data", "Foo", bar = "Bar")
 */
DOCBLOCK;

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "bar"\s.*\sAvailable named arguments: name/'
        );

        $parser->parse($docblock);
    }

    public function testAnnotationWithConstructorWithVariadicParamPassOneNamedArgument(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam(name = "Some data", data = "Foo")
 */
DOCBLOCK;

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessageMatches(
            '/does not have a property named "data"\s.*\sAvailable named arguments: name/'
        );

        $parser->parse($docblock);
    }

    public function testAnnotationWithConstructorWithVariadicParamPassPositionalArguments(): void
    {
        $parser   = $this->createTestParser();
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam("Some data", "Foo", "Bar")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertInstanceOf(SomeAnnotationWithConstructorWithVariadicParam::class, $annot);

        self::assertSame('Some data', $annot->name);
        // Positional extra arguments will be ignored
        self::assertSame([], $annot->data);
    }

    public function testAnnotationWithConstructorWithVariadicParamNoArgs(): void
    {
        $parser = $this->createTestParser();

        // Without variadic arguments
        $docblock = <<<'DOCBLOCK'
/**
 * @SomeAnnotationWithConstructorWithVariadicParam("Some data")
 */
DOCBLOCK;

        $result = $parser->parse($docblock);
        self::assertCount(1, $result);
        $annot = $result[0];

        self::assertInstanceOf(SomeAnnotationWithConstructorWithVariadicParam::class, $annot);

        self::assertSame('Some data', $annot->name);
        self::assertSame([], $annot->data);
    }

    /**
     * Override for BC with PHPUnit <8
     */
    public function expectExceptionMessageMatches(string $regularExpression): void
    {
        if (method_exists(get_parent_class($this), 'expectExceptionMessageMatches')) {
            parent::expectExceptionMessageMatches($regularExpression);
        } else {
            parent::expectExceptionMessageRegExp($regularExpression);
        }
    }
}

/** @Annotation */
class NamedAnnotation implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    private $foo;
    /** @var int */
    private $bar;

    public function __construct(string $foo, int $bar = 1234)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class AnotherNamedAnnotation
{
    /** @var string */
    private $foo;
    /** @var int */
    private $bar;
    /** @var string */
    private $baz;

    public function __construct(string $foo, int $bar = 1234, string $baz = 'baz')
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): int
    {
        return $this->bar;
    }

    public function getBaz(): string
    {
        return $this->baz;
    }
}

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class NamedAnnotationWithArray
{
    /** @var mixed[] */
    private $foo;
    /** @var int */
    private $bar;

    /**
     * @param mixed[] $foo
     */
    public function __construct(array $foo, int $bar = 1234)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    /** @return mixed[] */
    public function getFoo(): array
    {
        return $this->foo;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class SomeAnnotationWithConstructorWithVariadicParam
{
    public function __construct(string $name, string ...$data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /** @var string[] */
    public $data;

    /** @var string */
    public $name;
}

/** @Annotation */
class SettingsAnnotation
{
    /** @var mixed[] */
    public $settings;

    /**
     * @param mixed[] $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }
}

/** @Annotation */
class SomeAnnotationClassNameWithoutConstructor
{
    /** @var mixed */
    public $data;

    /** @var mixed */
    public $name;
}

/** @Annotation */
class SomeAnnotationWithConstructorWithoutParams
{
    public function __construct()
    {
        $this->data = 'Some data';
    }

    /** @var mixed */
    public $data;

    /** @var mixed */
    public $name;
}

/** @Annotation */
class SomeAnnotationClassNameWithoutConstructorAndProperties
{
}

/**
 * @Annotation
 * @Target("Foo")
 */
class AnnotationWithInvalidTargetDeclaration
{
}

/**
 * @Annotation
 * @Target
 */
class AnnotationWithTargetEmpty
{
}

/** @Annotation */
class AnnotationExtendsAnnotationTargetAll extends AnnotationTargetAll
{
}

/** @Annotation */
class Name extends Annotation
{
    /** @var mixed */
    public $foo;
}

/** @Annotation */
class Marker
{
    /** @var mixed */
    public $value;
}

namespace Doctrine\Tests\Common\Annotations\FooBar;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
class Name extends Annotation
{
}
