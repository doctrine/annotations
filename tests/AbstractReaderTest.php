<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\AnnotationsTests\TestCase;

use Doctrine\AnnotationsTests\Fixtures\Reader\TestParentClass;
use Doctrine\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Annotation;
use Doctrine\Annotations\Reader;
use ReflectionClass;

require  __DIR__ . '/Fixtures/Reader/TopLevelAnnotation.php';

abstract class AbstractReaderTest extends TestCase
{
    public function getReflectionClass()
    {
        return new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\DummyClass');
    }

    public function testAnnotations()
    {
        $class     = $this->getReflectionClass();
        $reader    = $this->getReader();
        $annotName = 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation';

        $this->assertEquals(1, count($reader->getClassAnnotations($class)));
        $this->assertInstanceOf($annotName, $annot = $reader->getClassAnnotation($class, $annotName));
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
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Reader\DummyJoinTable', $joinTableAnnot = $reader->getPropertyAnnotation($field2Prop, 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyJoinTable'));
        $this->assertEquals(1, count($joinTableAnnot->joinColumns));
        $this->assertEquals(1, count($joinTableAnnot->inverseJoinColumns));
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Reader\DummyJoinColumn', $joinTableAnnot->joinColumns[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Reader\DummyJoinColumn', $joinTableAnnot->inverseJoinColumns[0]);
        $this->assertEquals('col1', $joinTableAnnot->joinColumns[0]->name);
        $this->assertEquals('col2', $joinTableAnnot->joinColumns[0]->referencedColumnName);
        $this->assertEquals('col3', $joinTableAnnot->inverseJoinColumns[0]->name);
        $this->assertEquals('col4', $joinTableAnnot->inverseJoinColumns[0]->referencedColumnName);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField1'), 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation');
        $this->assertEquals('', $dummyAnnot->dummyValue);
        $this->assertEquals(array(1, 2, 'three'), $dummyAnnot->value);

        $dummyAnnot = $reader->getPropertyAnnotation($class->getProperty('field1'), 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation');
        $this->assertEquals('fieldHello', $dummyAnnot->dummyValue);

        $classAnnot = $reader->getClassAnnotation($class, 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation');
        $this->assertEquals('hello', $classAnnot->dummyValue);
    }

    public function testAnnotationsWithValidTargets()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithValidAnnotationTarget');

        $this->assertCount(1, $reader->getClassAnnotations($class));
        $this->assertCount(1, $reader->getPropertyAnnotations($class->getProperty('foo')));
        $this->assertCount(1, $reader->getPropertyAnnotations($class->getProperty('nested')));
        $this->assertCount(1, $reader->getMethodAnnotations($class->getMethod('someFunction')));
    }

    public function testAnnotationsWithVarType()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType');

        $this->assertCount(1, ($fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertCount(1, ($barAnnot = $reader->getMethodAnnotations($class->getMethod('bar'))));

        $this->assertInternalType('string',  $fooAnnot[0]->string);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', $barAnnot[0]->annotation);
    }

    public function testAtInDescription()
    {
        $reader = $this->getReader();
        $class  = new ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAtInDescriptionAndAnnotation');

        $this->assertCount(1, ($fooAnnot = $reader->getPropertyAnnotations($class->getProperty('foo'))));
        $this->assertCount(1, ($barAnnot = $reader->getPropertyAnnotations($class->getProperty('bar'))));

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod', $fooAnnot[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod', $barAnnot[0]);
    }

    public function testClassWithWithDanglingComma()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassWithDanglingComma');

        $this->assertCount(1, $reader->getClassAnnotations($class));
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TargetNotAllowedException
     * @expectedExceptionMessage Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod is not allowed to be declared on class Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtClass. You may only use this annotation on these code elements: METHOD,PROPERTY.
     */
    public function testClassWithInvalidAnnotationTargetAtClassDocBlock()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtClass');

        $reader->getClassAnnotations($class);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TargetNotAllowedException
     * @expectedExceptionMessage Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass is not allowed to be declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$foo. You may only use this annotation on these code elements: CLASS.
     */
    public function testClassWithInvalidAnnotationTargetAtPropertyDocBlock()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty', 'foo');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TargetNotAllowedException
     * @expectedExceptionMessage Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAnnotation is not allowed to be declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty::$bar. You may only use this annotation on these code elements: ANNOTATION.
     */
    public function testClassWithInvalidNestedAnnotationTargetAtPropertyDocBlock()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtProperty', 'bar');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TargetNotAllowedException
     * @expectedExceptionMessage Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass is not allowed to be declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtMethod::functionName(). You may only use this annotation on these code elements: CLASS.
     */
    public function testClassWithInvalidAnnotationTargetAtMethodDocBlock()
    {
        $reader = $this->getReader();
        $method = new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithInvalidAnnotationTargetAtMethod', 'functionName');

        $reader->getMethodAnnotations($method);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse class Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithTargetSyntaxError
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 32
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtClassDocBlock()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError');

        $reader->getClassAnnotations($class);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse class Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithTargetSyntaxError
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 32
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtPropertyDocBlock()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError','foo');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse class Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithTargetSyntaxError
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 32
     */
    public function testClassWithAnnotationWithTargetSyntaxErrorAtMethodDocBlock()
    {
        $reader = $this->getReader();
        $method = new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithTargetSyntaxError','bar');

        $reader->getMethodAnnotations($method);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TypeMismatchException
     * @expectedExceptionMessage Attribute "string" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType::$invalidProperty expects a(n) string, but got integer.
     */
    public function testClassWithPropertyInvalidVarTypeError()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType', 'invalidProperty');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\TypeMismatchException
     * @expectedExceptionMessage Attribute "annotation" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType::invalidMethod() expects a(n) Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll, but got an instance of Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAnnotation.
     */
    public function testClassWithMethodInvalidVarTypeError()
    {
        $reader = $this->getReader();
        $method = new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationWithVarType','invalidMethod');

        $reader->getMethodAnnotations($method);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse class Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassSyntaxError
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 26
     */
    public function testClassSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassSyntaxError');

        $reader->getClassAnnotations($class);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse method Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassMethodSyntaxError::foo()
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 30
     */
    public function testMethodSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $method = new \ReflectionMethod('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassMethodSyntaxError', 'foo');

        $reader->getMethodAnnotations($method);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ParserException
     * @expectedExceptionMessage Fail to parse property Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassPropertySyntaxError::$foo
     * @expectedExceptionMessage Unrecognized token ")" at line 1 and column 30
     */
    public function testPropertySyntaxErrorContext()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassPropertySyntaxError', 'foo');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClass2', 'id');
        $result   = $reader->getPropertyAnnotations($property);

        $this->assertCount(3, $result);
    }

    public function testNonAnnotationProblem()
    {
        $reader   = $this->getReader();
        $name     = 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation';
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\DummyClassNonAnnotationProblem', 'foo');
        $result   = $reader->getPropertyAnnotation($property, $name);

        $this->assertInstanceOf($name, $result);
    }

    public function testIncludeIgnoreAnnotation()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\ClassWithIgnoreAnnotation', 'foo');
        $result   = $reader->getPropertyAnnotations($property);

        $this->assertEmpty($result);
    }

    public function testImportWithConcreteAnnotation()
    {
        $reader      = $this->getReader();
        $name        = 'Doctrine\AnnotationsTests\Fixtures\Reader\DummyAnnotation';
        $property    = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\TestImportWithConcreteAnnotation', 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        $annotation  = $reader->getPropertyAnnotation($property, $name);

        $this->assertCount(1, $annotations);
        $this->assertInstanceOf($name, $annotation);
    }

    public function testImportWithInheritance()
    {
        $reader = $this->getReader();
        $class  = new TestParentClass();
        $ref    = new \ReflectionClass($class);

        $childAnnotations  = $reader->getPropertyAnnotations($ref->getProperty('child'));
        $parentAnnotations = $reader->getPropertyAnnotations($ref->getProperty('parent'));

        $this->assertCount(1, $childAnnotations);
        $this->assertCount(1, $parentAnnotations);

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Reader\Foo\Name', $childAnnotations[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Reader\Bar\Name', $parentAnnotations[0]);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ClassNotFoundException
     * @expectedExceptionMessage The annotation "@NameFoo" in property Doctrine\AnnotationsTests\Fixtures\Reader\TestAnnotationNotImportedClass::$field was never imported. Did you maybe forget to add a "use" statement for this annotation ?
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\TestAnnotationNotImportedClass', 'field');

        $reader->getPropertyAnnotations($property);
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ClassNotFoundException
     * @expectedExceptionMessage The annotation "@Foo\Bar\Name" in property Doctrine\AnnotationsTests\Fixtures\Reader\TestNonExistentAnnotationClass::$field was never imported. Did you maybe forget to add a "use" statement for this annotation ?
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\TestNonExistentAnnotationClass', 'field');

        $reader->getPropertyAnnotations($property);
    }

    public function testTopLevelAnnotation()
    {
        $reader   = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\AnnotationsTests\Fixtures\Reader\TestTopLevelAnnotationClass', 'field');
        $result   = $reader->getPropertyAnnotations($property);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('\TopLevelAnnotation', $result[0]);
    }

    public function testIgnoresAnnotationsNotPrefixedWithWhitespace()
    {
        $reader = $this->getReader();
        $name   = 'Doctrine\AnnotationsTests\Fixtures\Reader\Name';
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Reader\TestIgnoresNonAnnotationsClass');

        $result = $reader->getClassAnnotation($class, $name);
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
     * @expectedException \Doctrine\Annotations\Exception\InvalidAnnotationException
     * @expectedExceptionMessage The class "Doctrine\AnnotationsTests\Fixtures\NoAnnotation" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "Doctrine\AnnotationsTests\Fixtures\NoAnnotation". If it is indeed no annotation, then you need to add @IgnoreAnnotation("NoAnnotation") to the _class_ doc comment of class Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageClass.
     */
    public function testErrorWhenInvalidAnnotationIsUsed()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageClass');

        $reader->getClassAnnotations($class);
    }

    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\InvalidAnnotationUsageButIgnoredClass');
        $result = $reader->getClassAnnotations($class);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\Route', $result[0]);
    }

    /**
     * @group DDC-1660
     * @group regression
     */
    public function testInvalidAnnotationButIgnored()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassDDC1660');

        $this->assertCount(0, $reader->getClassAnnotations($class));
        $this->assertCount(0, $reader->getMethodAnnotations($class->getMethod('bar')));
        $this->assertCount(0, $reader->getPropertyAnnotations($class->getProperty('foo')));
    }

    public function testAnnotationEnumeratorException()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum');

        $bar = $reader->getMethodAnnotations($class->getMethod('bar'));
        $foo = $reader->getPropertyAnnotations($class->getProperty('foo'));

        $this->assertCount(1, $bar);
        $this->assertCount(1, $foo);

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum', $bar[0]);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum', $foo[0]);

        try {
            $reader->getPropertyAnnotations($class->getProperty('invalidProperty'));
            $this->fail();
        } catch (\Doctrine\Annotations\Exception\TypeMismatchException $exc) {
            $this->assertEquals('Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum declared on property Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum::$invalidProperty accept only [ONE, TWO, THREE], but got FOUR.', $exc->getMessage());
        }

        try {
            $reader->getMethodAnnotations($class->getMethod('invalidMethod'));
            $this->fail();
        } catch (\Doctrine\Annotations\Exception\TypeMismatchException $exc) {
            $this->assertEquals('Attribute "value" of @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum declared on method Doctrine\AnnotationsTests\Fixtures\ClassWithAnnotationEnum::invalidMethod() accept only [ONE, TWO, THREE], but got 5.', $exc->getMessage());
        }
    }

    /**
     * @group DCOM-106
     */
    public function testIgnoreFixMeAndUpperCaseToDo()
    {
        $reader = $this->getReader();
        $class  = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Reader\DCOM106');
        $result = $reader->getClassAnnotations($class);

        $this->assertEmpty($result);
    }

    /**
     * @return AnnotationReader
     */
    abstract protected function getReader();
}
