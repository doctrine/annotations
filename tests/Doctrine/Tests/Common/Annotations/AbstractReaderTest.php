<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;
use ReflectionClass, Doctrine\Common\Annotations\AnnotationReader;

require_once __DIR__ . '/TopLevelAnnotation.php';

abstract class AbstractReaderTest extends TestCase
{
    public function getReflectionClass()
    {
        return new ReflectionClass(DummyClass::class);
    }

    public function testAnnotations()
    {
        $class = $this->getReflectionClass();
        $reader = $this->getReader();

        self::assertCount(1, $reader->getClassAnnotations($class));
        self::assertInstanceOf($annotName = DummyAnnotation::class, $annot = $reader->getClassAnnotation($class, $annotName));
        self::assertEquals('hello', $annot->dummyValue);

        $field1Prop = $class->getProperty('field1');
        $propAnnots = $reader->getPropertyAnnotations($field1Prop);
        self::assertCount(1, $propAnnots);
        self::assertInstanceOf($annotName, $annot = $reader->getPropertyAnnotation($field1Prop, $annotName));
        self::assertEquals('fieldHello', $annot->dummyValue);

        $getField1Method = $class->getMethod('getField1');
        $methodAnnots = $reader->getMethodAnnotations($getField1Method);
        self::assertCount(1, $methodAnnots);
        self::assertInstanceOf($annotName, $annot = $reader->getMethodAnnotation($getField1Method, $annotName));
        self::assertEquals([1, 2, 'three'], $annot->value);

        $field2Prop = $class->getProperty('field2');
        $propAnnots = $reader->getPropertyAnnotations($field2Prop);
        self::assertCount(1, $propAnnots);
        self::assertInstanceOf($annotName = DummyJoinTable::class, $joinTableAnnot = $reader->getPropertyAnnotation($field2Prop, $annotName));
        self::assertCount(1, $joinTableAnnot->joinColumns);
        self::assertCount(1, $joinTableAnnot->inverseJoinColumns);
        self::assertInstanceOf(DummyJoinColumn::class, $joinTableAnnot->joinColumns[0]);
        self::assertInstanceOf(DummyJoinColumn::class, $joinTableAnnot->inverseJoinColumns[0]);
        self::assertEquals('col1', $joinTableAnnot->joinColumns[0]->name);
        self::assertEquals('col2', $joinTableAnnot->joinColumns[0]->referencedColumnName);
        self::assertEquals('col3', $joinTableAnnot->inverseJoinColumns[0]->name);
        self::assertEquals('col4', $joinTableAnnot->inverseJoinColumns[0]->referencedColumnName);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField1'), DummyAnnotation::class);
        self::assertEquals('', $dummyAnnot->dummyValue);
        self::assertEquals([1, 2, 'three'], $dummyAnnot->value);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField3'), DummyAnnotation::class);
        self::assertEquals('\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d', $dummyAnnot->value);

        $dummyAnnot = $reader->getPropertyAnnotation($class->getProperty('field1'), DummyAnnotation::class);
        self::assertEquals('fieldHello', $dummyAnnot->dummyValue);

        $classAnnot = $reader->getClassAnnotation($class, DummyAnnotation::class);
        self::assertEquals('hello', $classAnnot->dummyValue);
    }

    public function testAnnotationsWithValidTargets()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass(Fixtures\ClassWithValidAnnotationTarget::class);

        self::assertCount(1, $reader->getClassAnnotations($class));
        self::assertCount(1, $reader->getPropertyAnnotations($class->getProperty('foo')));
        self::assertCount(1, $reader->getMethodAnnotations($class->getMethod('someFunction')));
        self::assertCount(1, $reader->getPropertyAnnotations($class->getProperty('nested')));
    }

    public function testAnnotationsWithVarType()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass(Fixtures\ClassWithAnnotationWithVarType::class);

        self::assertCount(1, $fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo')));
        self::assertCount(1, $barAnnot = $reader->getMethodAnnotations($class->getMethod('bar')));

        self::assertInternalType('string',  $fooAnnot[0]->string);
        self::assertInstanceOf(Fixtures\AnnotationTargetAll::class, $barAnnot[0]->annotation);
    }

    public function testAtInDescription()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass(Fixtures\ClassWithAtInDescriptionAndAnnotation::class);

        self::assertCount(1, $fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo')));
        self::assertCount(1, $barAnnot = $reader->getPropertyAnnotations($class->getProperty('bar')));

        self::assertInstanceOf(Fixtures\AnnotationTargetPropertyMethod::class, $fooAnnot[0]);
        self::assertInstanceOf(Fixtures\AnnotationTargetPropertyMethod::class, $barAnnot[0]);
    }

    public function testClassWithWithDanglingComma()
    {
        $reader = $this->getReader();
        $annots = $reader->getClassAnnotations(new \ReflectionClass(DummyClassWithDanglingComma::class));

        self::assertCount(1, $annots);
    }

     /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetPropertyMethod is not allowed to be declared on class Doctrine\Tests\Common\Annotations\Fixtures\ClassWithInvalidAnnotationTargetAtClass. You may only use this annotation on these code elements: METHOD, PROPERTY
     */
    public function testClassWithInvalidAnnotationTargetAtClassDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass(Fixtures\ClassWithInvalidAnnotationTargetAtClass::class));
    }

    public function testClassWithWithInclude()
    {
        $reader = $this->getReader();
        $annots = $reader->getClassAnnotations(new \ReflectionClass(Fixtures\ClassWithRequire::class));
        self::assertCount(1, $annots);
    }

     /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetClass is not allowed to be declared on property Doctrine\Tests\Common\Annotations\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$foo. You may only use this annotation on these code elements: CLASS
     */
    public function testClassWithInvalidAnnotationTargetAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class, 'foo'));
    }

     /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetAnnotation is not allowed to be declared on property Doctrine\Tests\Common\Annotations\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$bar. You may only use this annotation on these code elements: ANNOTATION
     */
    public function testClassWithInvalidNestedAnnotationTargetAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(Fixtures\ClassWithInvalidAnnotationTargetAtProperty::class, 'bar'));
    }

     /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetClass is not allowed to be declared on method Doctrine\Tests\Common\Annotations\Fixtures\ClassWithInvalidAnnotationTargetAtMethod::functionName(). You may only use this annotation on these code elements: CLASS
     */
    public function testClassWithInvalidAnnotationTargetAtMethodDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod(Fixtures\ClassWithInvalidAnnotationTargetAtMethod::class, 'functionName'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtClassDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass(Fixtures\ClassWithAnnotationWithTargetSyntaxError::class));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(Fixtures\ClassWithAnnotationWithTargetSyntaxError::class,'foo'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtMethodDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod(Fixtures\ClassWithAnnotationWithTargetSyntaxError::class,'bar'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Type Error] Attribute "string" of @AnnotationWithVarType declared on property Doctrine\Tests\Common\Annotations\Fixtures\ClassWithAnnotationWithVarType::$invalidProperty expects a(n) string, but got integer.
     */
    public function testClassWithPropertyInvalidVarTypeError()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass(Fixtures\ClassWithAnnotationWithVarType::class);

        $reader->getPropertyAnnotations($class->getProperty('invalidProperty'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage [Type Error] Attribute "annotation" of @AnnotationWithVarType declared on method Doctrine\Tests\Common\Annotations\Fixtures\ClassWithAnnotationWithVarType::invalidMethod() expects a(n) \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll, but got an instance of Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAnnotation.
     */
    public function testClassWithMethodInvalidVarTypeError()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass(Fixtures\ClassWithAnnotationWithVarType::class);

        $reader->getMethodAnnotations($class->getMethod('invalidMethod'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in class Doctrine\Tests\Common\Annotations\DummyClassSyntaxError.
     */
    public function testClassSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass(DummyClassSyntaxError::class));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in method Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError::foo().
     */
    public function testMethodSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod(DummyClassMethodSyntaxError::class, 'foo'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in property Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError::$foo.
     */
    public function testPropertySyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(DummyClassPropertySyntaxError::class, 'foo'));
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->getReader();
        $annots = $reader->getPropertyAnnotations(new \ReflectionProperty(DummyClass2::class, 'id'));
        self::assertCount(3, $annots);
    }

    public function testNonAnnotationProblem()
    {
        $reader = $this->getReader();

        self::assertNotNull($annot = $reader->getPropertyAnnotation(new \ReflectionProperty(DummyClassNonAnnotationProblem::class, 'foo'), $name = DummyAnnotation::class));
        self::assertInstanceOf($name, $annot);
    }

    public function testIncludeIgnoreAnnotation()
    {
        $reader = $this->getReader();

        $reader->getPropertyAnnotations(new \ReflectionProperty(Fixtures\ClassWithIgnoreAnnotation::class, 'foo'));
        self::assertFalse(class_exists(Fixtures\IgnoreAnnotationClass::class, false));
    }

    public function testImportWithConcreteAnnotation()
    {
        $reader = $this->getReader();
        $property = new \ReflectionProperty(TestImportWithConcreteAnnotation::class, 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        self::assertCount(1, $annotations);
        self::assertNotNull($reader->getPropertyAnnotation($property, DummyAnnotation::class));
    }

    public function testImportWithInheritance()
    {
        $reader = $this->getReader();

        $class = new TestParentClass();
        $ref = new \ReflectionClass($class);

        $childAnnotations = $reader->getPropertyAnnotations($ref->getProperty('child'));
        self::assertCount(1, $childAnnotations);
        self::assertInstanceOf(Foo\Name::class, reset($childAnnotations));

        $parentAnnotations = $reader->getPropertyAnnotations($ref->getProperty('parent'));
        self::assertCount(1, $parentAnnotations);
        self::assertInstanceOf(Bar\Name::class, reset($parentAnnotations));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@NameFoo" in property Doctrine\Tests\Common\Annotations\TestAnnotationNotImportedClass::$field was never imported.
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(TestAnnotationNotImportedClass::class, 'field'));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@Foo\Bar\Name" in property Doctrine\Tests\Common\Annotations\TestNonExistentAnnotationClass::$field was never imported.
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty(TestNonExistentAnnotationClass::class, 'field'));
    }

    public function testTopLevelAnnotation()
    {
        $reader = $this->getReader();
        $annotations = $reader->getPropertyAnnotations(new \ReflectionProperty(TestTopLevelAnnotationClass::class, 'field'));

        self::assertCount(1, $annotations);
        self::assertInstanceOf(\TopLevelAnnotation::class, reset($annotations));
    }

    public function testIgnoresAnnotationsNotPrefixedWithWhitespace()
    {
        $reader = $this->getReader();

        $annotation = $reader->getClassAnnotation(new \ReflectionClass(new TestIgnoresNonAnnotationsClass()), Name::class);
        self::assertInstanceOf(Name::class, $annotation);
    }

    private static $testResetsPhpParserAfterUseRun = false;

    /**
     * When getUseStatements isn't available on ReflectionClass the PhpParser has to use token_get_all(). If that
     * happens various PHP compiler globals get set, and these can have seriously bad effects on the next file to be
     * parsed.
     * Notably the doc_comment compiler global can end up containing a docblock comment. The next file to be parsed
     * on an include() will have this docblock comment attached to the first thing in the file that the compiler
     * considers to own comments. If this is a class then any later calls to getDocComment() for that class will have
     * undesirable effects. *sigh*
     */
    public function testResetsPhpParserAfterUse()
    {
        // If someone has already included our main test fixture this test is invalid. It's important that our require
        // causes this file to be parsed and compiled at a certain point.
        self::assertFalse(!self::$testResetsPhpParserAfterUseRun && class_exists(\Doctrine_Tests_Common_Annotations_Fixtures_ClassNoNamespaceNoComment::class), 'Test invalid if class has already been compiled');
        self::$testResetsPhpParserAfterUseRun = true;

        $reader = $this->getReader();

        // First make sure the annotation cache knows about the annotations we want to use.
        // If we don't do this then loading of annotations into the cache will cause the parser to get out of the bad
        // state we want to test.
        $class  = new ReflectionClass(Fixtures\ClassWithValidAnnotationTarget::class);
        $reader->getClassAnnotations($class);

        // Now import an incredibly dull class which makes use of the same class level annotation that the previous class does.
        $class  = new ReflectionClass(Fixtures\ClassWithClassAnnotationOnly::class);
        $annotations = $reader->getClassAnnotations($class);

        // This include needs to be here since we need the PHP compiler to run over it as the next thing the PHP
        // parser sees since PhpParser called token_get_all() on the intro to ClassWithClassAnnotationOnly.
        // Our test class cannot be in a namespace (some versions of PHP reset the doc_comment compiler global when
        // you hit a namespace declaration), so cannot be autoloaded.
        require_once __DIR__ . '/Fixtures/ClassNoNamespaceNoComment.php';

        // So, hopefully, if all has gone OK, our class with class annotations should actually have them.
        // If this fails then something is quite badly wrong elsewhere.
        // Note that if this happens before the require it can cause other PHP files to be included, resetting the
        // compiler global state, and invalidating this test case.
        self::assertNotEmpty($annotations);

        $annotations = $reader->getClassAnnotations(new \ReflectionClass(new \Doctrine_Tests_Common_Annotations_Fixtures_ClassNoNamespaceNoComment()));
        // And if our workaround for this bug is OK, our class with no doc comment should not have any class annotations.
        self::assertEmpty($annotations);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The class "Doctrine\Tests\Common\Annotations\Fixtures\NoAnnotation" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "Doctrine\Tests\Common\Annotations\Fixtures\NoAnnotation". If it is indeed no annotation, then you need to add @IgnoreAnnotation("NoAnnotation") to the _class_ doc comment of class Doctrine\Tests\Common\Annotations\Fixtures\InvalidAnnotationUsageClass.
     */
    public function testErrorWhenInvalidAnnotationIsUsed()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\InvalidAnnotationUsageClass::class);
        $reader->getClassAnnotations($ref);
    }

    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\InvalidAnnotationUsageButIgnoredClass::class);
        $annots = $reader->getClassAnnotations($ref);

        self::assertCount(2, $annots);
    }

    /**
     * @group DDC-1660
     * @group regression
     */
    public function testInvalidAnnotationButIgnored()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass(Fixtures\ClassDDC1660::class);

        self::assertTrue(class_exists(Fixtures\Annotation\Version::class));
        self::assertEmpty($reader->getClassAnnotations($class));
        self::assertEmpty($reader->getMethodAnnotations($class->getMethod('bar')));
        self::assertEmpty($reader->getPropertyAnnotations($class->getProperty('foo')));
    }

    public function testAnnotationEnumeratorException()
    {
        $reader     = $this->getReader();
        $class      = new \ReflectionClass(Fixtures\ClassWithAnnotationEnum::class);

        self::assertCount(1, $bar = $reader->getMethodAnnotations($class->getMethod('bar')));
        self::assertCount(1, $foo = $reader->getPropertyAnnotations($class->getProperty('foo')));

        self::assertInstanceOf(Fixtures\AnnotationEnum::class, $bar[0]);
        self::assertInstanceOf(Fixtures\AnnotationEnum::class, $foo[0]);

        try {
            $reader->getPropertyAnnotations($class->getProperty('invalidProperty'));
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertEquals('[Enum Error] Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum declared on property Doctrine\Tests\Common\Annotations\Fixtures\ClassWithAnnotationEnum::$invalidProperty accept only [ONE, TWO, THREE], but got FOUR.', $exc->getMessage());
        }

        try {
            $reader->getMethodAnnotations($class->getMethod('invalidMethod'));
            $this->fail();
        } catch (AnnotationException $exc) {
            self::assertEquals('[Enum Error] Attribute "value" of @Doctrine\Tests\Common\Annotations\Fixtures\AnnotationEnum declared on method Doctrine\Tests\Common\Annotations\Fixtures\ClassWithAnnotationEnum::invalidMethod() accept only [ONE, TWO, THREE], but got 5.', $exc->getMessage());
        }
    }

    /**
     * @group DCOM-106
     */
    public function testIgnoreFixMeAndUpperCaseToDo()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(DCOM106::class);

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    public function testWillSkipAnnotationsContainingDashes()
    {
        self::assertEmpty(
            $this
                ->getReader()
                ->getClassAnnotations(new \ReflectionClass(
                    Fixtures\ClassWithInvalidAnnotationContainingDashes::class
                ))
        );
    }

    public function testWillFailOnAnnotationConstantReferenceContainingDashes()
    {
        $reader     = $this->getReader();
        $reflection = new \ReflectionClass(Fixtures\ClassWithAnnotationConstantReferenceWithDashes::class);

        $this->expectExceptionMessage(
            '[Syntax Error] Expected Doctrine\Common\Annotations\DocLexer::T_CLOSE_PARENTHESIS, got \'-\' at'
            . ' position 14 in class ' . Fixtures\ClassWithAnnotationConstantReferenceWithDashes::class . '.'
        );

        $reader->getClassAnnotations($reflection);
    }

    /**
     * @return AnnotationReader
     */
    abstract protected function getReader();
}

/**
 * @parseAnnotation("var")
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 */
class TestParseAnnotationClass
{
    /**
     * @var
     */
    private $field;
}

/**
 * @Name
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class TestIgnoresNonAnnotationsClass
{
}

class TestTopLevelAnnotationClass
{
    /**
     * @\TopLevelAnnotation
     */
    private $field;
}

class TestNonExistentAnnotationClass
{
    /**
     * @Foo\Bar\Name
     */
    private $field;
}

class TestAnnotationNotImportedClass
{
    /**
     * @NameFoo
     */
    private $field;
}

class TestChildClass
{
    /**
     * @\Doctrine\Tests\Common\Annotations\Foo\Name(name = "foo")
     */
    protected $child;
}

class TestParentClass extends TestChildClass
{
    /**
     * @\Doctrine\Tests\Common\Annotations\Bar\Name(name = "bar")
     */
    private $parent;
}

class TestImportWithConcreteAnnotation
{
    /**
     * @DummyAnnotation(dummyValue = "bar")
     */
    private $field;
}

/**
 * @ignoreAnnotation("var")
 */
class DummyClass2 {
    /**
     * @DummyId @DummyColumn(type="integer") @DummyGeneratedValue
     * @var integer
     */
    private $id;
}

/** @Annotation */
class DummyId extends Annotation {}
/** @Annotation */
class DummyColumn extends Annotation {
    public $type;
}
/** @Annotation */
class DummyGeneratedValue extends Annotation {}
/** @Annotation */
class DummyAnnotation extends Annotation {
    public $dummyValue;
}

/**
 * @api
 * @Annotation
 */
class DummyAnnotationWithIgnoredAnnotation extends Annotation {
    public $dummyValue;
}

/** @Annotation */
class DummyJoinColumn extends Annotation {
    public $name;
    public $referencedColumnName;
}
/** @Annotation */
class DummyJoinTable extends Annotation {
    public $name;
    public $joinColumns;
    public $inverseJoinColumns;
}

/**
 * @DummyAnnotation(dummyValue = "bar",)
 */
class DummyClassWithDanglingComma
{
}

/**
 * @DummyAnnotation(@)
 */
class DummyClassSyntaxError
{

}

class DummyClassMethodSyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public function foo()
    {

    }
}

class DummyClassPropertySyntaxError
{
    /**
     * @DummyAnnotation(@)
     */
    public $foo;
}

/**
 * @ignoreAnnotation({"since", "var"})
 */
class DummyClassNonAnnotationProblem
{
    /**
     * @DummyAnnotation
     *
     * @var \Test
     * @since 0.1
     */
    public $foo;
}


/**
* @DummyAnnotation Foo bar <foobar@1domain.com>
*/
class DummyClassWithEmail
{

}


/**
 * @fixme public
 * @TODO
 */
class DCOM106
{

}

namespace Doctrine\Tests\Common\Annotations\Foo;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
class Name extends Annotation
{
    public $name;
}

namespace Doctrine\Tests\Common\Annotations\Bar;

use Doctrine\Common\Annotations\Annotation;

/** @Annotation */
class Name extends Annotation
{
    public $name;
}
