<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Annotation;
use Doctrine\Annotations\Reader;
use ReflectionClass;

/**
 * @group deprecated
 */
abstract class AbstractReaderTest extends \PHPUnit_Framework_TestCase
{
    public function getReflectionClass()
    {
        $className = 'Doctrine\AnnotationsTests\Fixtures\DummyClass';
        return new ReflectionClass($className);
    }

    public function testAnnotations()
    {
        $class = $this->getReflectionClass();
        $reader = $this->getReader();

        $this->assertEquals(1, count($reader->getClassAnnotations($class)));
        $this->assertInstanceOf($annotName = DummyAnnotation::CLASS, $annot = $reader->getClassAnnotation($class, $annotName));
        $this->assertEquals("hello", $annot->dummyValue);

        $field1Prop = $class->getProperty('field1');
        $propAnnots = $reader->getPropertyAnnotations($field1Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertInstanceOf($annotName, $annot = $reader->getPropertyAnnotation($field1Prop, $annotName));
        $this->assertEquals("fieldHello", $annot->dummyValue);

        $getField1Method = $class->getMethod('getField1');
        $methodAnnots = $reader->getMethodAnnotations($getField1Method);
        $this->assertEquals(1, count($methodAnnots));
        $this->assertInstanceOf($annotName, $annot = $reader->getMethodAnnotation($getField1Method, $annotName));
        $this->assertEquals(array(1, 2, "three"), $annot->value);

        $field2Prop = $class->getProperty('field2');
        $propAnnots = $reader->getPropertyAnnotations($field2Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertInstanceOf($annotName = DummyJoinTable::CLASS, $joinTableAnnot = $reader->getPropertyAnnotation($field2Prop, $annotName));
        $this->assertEquals(1, count($joinTableAnnot->joinColumns));
        $this->assertEquals(1, count($joinTableAnnot->inverseJoinColumns));
        $this->assertTrue($joinTableAnnot->joinColumns[0] instanceof DummyJoinColumn);
        $this->assertTrue($joinTableAnnot->inverseJoinColumns[0] instanceof DummyJoinColumn);
        $this->assertEquals('col1', $joinTableAnnot->joinColumns[0]->name);
        $this->assertEquals('col2', $joinTableAnnot->joinColumns[0]->referencedColumnName);
        $this->assertEquals('col3', $joinTableAnnot->inverseJoinColumns[0]->name);
        $this->assertEquals('col4', $joinTableAnnot->inverseJoinColumns[0]->referencedColumnName);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField1'), DummyAnnotation::CLASS);
        $this->assertEquals('', $dummyAnnot->dummyValue);
        $this->assertEquals(array(1, 2, 'three'), $dummyAnnot->value);

        $dummyAnnot = $reader->getPropertyAnnotation($class->getProperty('field1'), DummyAnnotation::CLASS);
        $this->assertEquals('fieldHello', $dummyAnnot->dummyValue);

        $classAnnot = $reader->getClassAnnotation($class, DummyAnnotation::CLASS);
        $this->assertEquals('hello', $classAnnot->dummyValue);
    }

    public function testAnnotationsWithValidTargets()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget');

        $this->assertEquals(1,count($reader->getClassAnnotations($class)));
        $this->assertEquals(1,count($reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertEquals(1,count($reader->getMethodAnnotations($class->getMethod('someFunction'))));
        $this->assertEquals(1,count($reader->getPropertyAnnotations($class->getProperty('nested'))));
    }

    public function testAnnotationsWithVarType()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType');

        $this->assertEquals(1,count($fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertEquals(1,count($barAnnot = $reader->getMethodAnnotations($class->getMethod('bar'))));

        $this->assertInternalType('string',  $fooAnnot[0]->string);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\AnnotationTargetAll', $barAnnot[0]->annotation);
    }

    public function testAtInDescription()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAtInDescriptionAndAnnotation');

        $this->assertEquals(1, count($fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertEquals(1, count($barAnnot = $reader->getPropertyAnnotations($class->getProperty('bar'))));

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\AnnotationTargetPropertyMethod', $fooAnnot[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\AnnotationTargetPropertyMethod', $barAnnot[0]);
    }

    public function testClassWithWithDanglingComma()
    {
        $reader = $this->getReader();
        $annots = $reader->getClassAnnotations(new \ReflectionClass('Doctrine\AnnotationsTests\DummyClassWithDanglingComma'));

        $this->assertCount(1, $annots);
    }

     /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetPropertyMethod is not allowed to be declared on class Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtClass. You may only use this annotation on these code elements: METHOD, PROPERTY
     */
    public function testClassWithInvalidAnnotationTargetAtClassDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtClass'));
    }

     /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetClass is not allowed to be declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$foo. You may only use this annotation on these code elements: CLASS
     */
    public function testClassWithInvalidAnnotationTargetAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty', 'foo'));
    }

     /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetAnnotation is not allowed to be declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$bar. You may only use this annotation on these code elements: ANNOTATION
     */
    public function testClassWithInvalidNestedAnnotationTargetAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty', 'bar'));
    }

     /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Semantical Error] Annotation @AnnotationTargetClass is not allowed to be declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtMethod::functionName(). You may only use this annotation on these code elements: CLASS
     */
    public function testClassWithInvalidAnnotationTargetAtMethodDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtMethod', 'functionName'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\AnnotationsTests\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtClassDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\AnnotationsTests\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtPropertyDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError','foo'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 24 in class @Doctrine\AnnotationsTests\Fixtures\AnnotationWithTargetSyntaxError.
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtMethodDocBlock()
    {
        $reader  = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError','bar'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Type Error] Attribute "string" of @AnnotationWithVarType declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType::$invalidProperty expects a(n) string, but got integer.
     */
    public function testClassWithPropertyInvalidVarTypeError()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType');

        $reader->getPropertyAnnotations($class->getProperty('invalidProperty'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage [Type Error] Attribute "annotation" of @AnnotationWithVarType declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType::invalidMethod() expects a(n) Doctrine\AnnotationsTests\Fixtures\AnnotationTargetAll, but got an instance of Doctrine\AnnotationsTests\Fixtures\AnnotationTargetAnnotation.
     */
    public function testClassWithMethodInvalidVarTypeError()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType');

        $reader->getMethodAnnotations($class->getMethod('invalidMethod'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in class Doctrine\AnnotationsTests\DummyClassSyntaxError.
     */
    public function testClassSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass('Doctrine\AnnotationsTests\DummyClassSyntaxError'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in method Doctrine\AnnotationsTests\DummyClassMethodSyntaxError::foo().
     */
    public function testMethodSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\AnnotationsTests\DummyClassMethodSyntaxError', 'foo'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in property Doctrine\AnnotationsTests\DummyClassPropertySyntaxError::$foo.
     */
    public function testPropertySyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\DummyClassPropertySyntaxError', 'foo'));
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->getReader();
        $annots = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\DummyClass2', 'id'));
        $this->assertEquals(3, count($annots));
    }

    public function testNonAnnotationProblem()
    {
        $reader = $this->getReader();

        $this->assertNotNull($annot = $reader->getPropertyAnnotation(new \ReflectionProperty('Doctrine\AnnotationsTests\DummyClassNonAnnotationProblem', 'foo'), $name = 'Doctrine\AnnotationsTests\DummyAnnotation'));
        $this->assertInstanceOf($name, $annot);
    }

    public function testIncludeIgnoreAnnotation()
    {
        $reader = $this->getReader();

        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithIgnoreAnnotation', 'foo'));
        $this->assertFalse(class_exists('Doctrine\AnnotationsTests\Fixtures\IgnoreAnnotationClass', false));
    }

    public function testImportWithConcreteAnnotation()
    {
        $reader = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\TestImportWithConcreteAnnotation', 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        $this->assertEquals(1, count($annotations));
        $this->assertNotNull($reader->getPropertyAnnotation($property, 'Doctrine\AnnotationsTests\DummyAnnotation'));
    }

    public function testImportWithInheritance()
    {
        $reader = $this->getReader();

        $class = new TestParentClass();
        $ref = new \ReflectionClass($class);

        $childAnnotations = $reader->getPropertyAnnotations($ref->getProperty('child'));
        $this->assertEquals(1, count($childAnnotations));
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Foo\Name', reset($childAnnotations));

        $parentAnnotations = $reader->getPropertyAnnotations($ref->getProperty('parent'));
        $this->assertEquals(1, count($parentAnnotations));
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Bar\Name', reset($parentAnnotations));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@NameFoo" in property Doctrine\AnnotationsTests\TestAnnotationNotImportedClass::$field was never imported.
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\TestAnnotationNotImportedClass', 'field'));
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@Foo\Bar\Name" in property Doctrine\AnnotationsTests\TestNonExistentAnnotationClass::$field was never imported.
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\TestNonExistentAnnotationClass', 'field'));
    }

    public function testTopLevelAnnotation()
    {
        $reader = $this->getReader();
        $annotations = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\AnnotationsTests\TestTopLevelAnnotationClass', 'field'));

        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('\TopLevelAnnotation', reset($annotations));
    }

    public function testIgnoresAnnotationsNotPrefixedWithWhitespace()
    {
        $reader = $this->getReader();

        $annotation = $reader->getClassAnnotation(new \ReflectionClass(new TestIgnoresNonAnnotationsClass()), 'Doctrine\AnnotationsTests\Name');
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Name', $annotation);
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
        $this->assertFalse(!self::$testResetsPhpParserAfterUseRun && class_exists('Doctrine_Tests_Common_Annotations_Fixtures_ClassNoNamespaceNoComment'), 'Test invalid if class has already been compiled');
        self::$testResetsPhpParserAfterUseRun = true;

        $reader = $this->getReader();

        // First make sure the annotation cache knows about the annotations we want to use.
        // If we don't do this then loading of annotations into the cache will cause the parser to get out of the bad
        // state we want to test.
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget');
        $reader->getClassAnnotations($class);

        // Now import an incredibly dull class which makes use of the same class level annotation that the previous class does.
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithClassAnnotationOnly');
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
        $this->assertNotEmpty($annotations);

        $annotations = $reader->getClassAnnotations(new \ReflectionClass(new \Doctrine_Tests_Common_Annotations_Fixtures_ClassNoNamespaceNoComment()));
        // And if our workaround for this bug is OK, our class with no doc comment should not have any class annotations.
        $this->assertEmpty($annotations);
    }

    /**
     * @expectedException \Doctrine\Annotations\AnnotationException
     * @expectedExceptionMessage The class "Doctrine\AnnotationsTests\Fixtures\NoAnnotation" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "Doctrine\AnnotationsTests\Fixtures\NoAnnotation". If it is indeed no annotation, then you need to add @IgnoreAnnotation("NoAnnotation") to the _class_ doc comment of class Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageClass.
     */
    public function testErrorWhenInvalidAnnotationIsUsed()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageClass');
        $reader->getClassAnnotations($ref);
    }

    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageButIgnoredClass');
        $annots = $reader->getClassAnnotations($ref);

        $this->assertEquals(2, count($annots));
    }

    /**
     * @group DDC-1660
     * @group regression
     */
    public function testInvalidAnnotationButIgnored()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassDDC1660');

        $this->assertTrue(class_exists('Doctrine\AnnotationsTests\Fixtures\Annotation\Version'));
        $this->assertCount(0, $reader->getClassAnnotations($class));
        $this->assertCount(0, $reader->getMethodAnnotations($class->getMethod('bar')));
        $this->assertCount(0, $reader->getPropertyAnnotations($class->getProperty('foo')));
    }

    public function testAnnotationEnumeratorException()
    {
        $reader     = $this->getReader();
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum');

        $this->assertCount(1, $bar = $reader->getMethodAnnotations($class->getMethod('bar')));
        $this->assertCount(1, $foo = $reader->getPropertyAnnotations($class->getProperty('foo')));

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\AnnotationEnum', $bar[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\AnnotationEnum', $foo[0]);

        try {
            $reader->getPropertyAnnotations($class->getProperty('invalidProperty'));
            $this->fail();
        } catch (\Doctrine\Annotations\AnnotationException $exc) {
            $this->assertEquals('[Enum Error] Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\AnnotationEnum declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum::$invalidProperty accept only [ONE, TWO, THREE], but got FOUR.', $exc->getMessage());
        }

        try {
            $reader->getMethodAnnotations($class->getMethod('invalidMethod'));
            $this->fail();
        } catch (\Doctrine\Annotations\AnnotationException $exc) {
            $this->assertEquals('[Enum Error] Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\AnnotationEnum declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum::invalidMethod() accept only [ONE, TWO, THREE], but got 5.', $exc->getMessage());
        }
    }

    /**
     * @group DCOM-106
     */
    public function testIgnoreFixMeAndUpperCaseToDo()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass('Doctrine\AnnotationsTests\DCOM106');
        $reader->getClassAnnotations($ref);
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
     * @\Doctrine\AnnotationsTests\Foo\Name(name = "foo")
     */
    protected $child;
}

class TestParentClass extends TestChildClass
{
    /**
     * @\Doctrine\AnnotationsTests\Bar\Name(name = "bar")
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
 * @ignoreAnnotation({"var"})
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

namespace Doctrine\AnnotationsTests\Foo;

/** @Annotation */
class Name extends \Doctrine\Annotations\Annotation
{
    public $name;
}

namespace Doctrine\AnnotationsTests\Bar;

/** @Annotation */
class Name extends \Doctrine\Annotations\Annotation
{
    public $name;
}

use Doctrine\Annotations\Annotation;

/** @Annotation */
class TopLevelAnnotation extends Annotation
{
}

