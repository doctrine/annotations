<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\DoctrineReader;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\Annotation\IgnorePhpDoc;
use ReflectionClass, Doctrine\Common\Annotations\AnnotationReader;

use Doctrine\Tests\Common\Annotations\DummyAnnotation;
use Doctrine\Tests\Common\Annotations\Name;
use Doctrine\Tests\Common\Annotations\DummyId;
use Doctrine\Tests\Common\Annotations\DummyJoinTable;
use Doctrine\Tests\Common\Annotations\DummyJoinColumn;
use Doctrine\Tests\Common\Annotations\DummyColumn;
use Doctrine\Tests\Common\Annotations\DummyGeneratedValue;

require_once __DIR__ . '/TopLevelAnnotation.php';

abstract class AbstractReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAnnotations()
    {
        $reader = $this->getReader();

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClass');
        $this->assertEquals(1, count($reader->getClassAnnotations($class)));
        $this->assertInstanceOf($annotName = 'Doctrine\Tests\Common\Annotations\DummyAnnotation', $annot = $reader->getClassAnnotation($class, $annotName));
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
        $this->assertInstanceOf($annotName = 'Doctrine\Tests\Common\Annotations\DummyJoinTable', $joinTableAnnot = $reader->getPropertyAnnotation($field2Prop, $annotName));
        $this->assertEquals(1, count($joinTableAnnot->joinColumns));
        $this->assertEquals(1, count($joinTableAnnot->inverseJoinColumns));
        $this->assertTrue($joinTableAnnot->joinColumns[0] instanceof DummyJoinColumn);
        $this->assertTrue($joinTableAnnot->inverseJoinColumns[0] instanceof DummyJoinColumn);
        $this->assertEquals('col1', $joinTableAnnot->joinColumns[0]->name);
        $this->assertEquals('col2', $joinTableAnnot->joinColumns[0]->referencedColumnName);
        $this->assertEquals('col3', $joinTableAnnot->inverseJoinColumns[0]->name);
        $this->assertEquals('col4', $joinTableAnnot->inverseJoinColumns[0]->referencedColumnName);

        $dummyAnnot = $reader->getMethodAnnotation($class->getMethod('getField1'), 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('', $dummyAnnot->dummyValue);
        $this->assertEquals(array(1, 2, 'three'), $dummyAnnot->value);

        $dummyAnnot = $reader->getPropertyAnnotation($class->getProperty('field1'), 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('fieldHello', $dummyAnnot->dummyValue);

        $classAnnot = $reader->getClassAnnotation($class, 'Doctrine\Tests\Common\Annotations\DummyAnnotation');
        $this->assertEquals('hello', $classAnnot->dummyValue);
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in class Doctrine\Tests\Common\Annotations\DummyClassSyntaxError.
     */
    public function testClassSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getClassAnnotations(new \ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClassSyntaxError'));
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in method Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError::foo().
     */
    public function testMethodSyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getMethodAnnotations(new \ReflectionMethod('Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError', 'foo'));
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Expected namespace separator or identifier, got ')' at position 18 in property Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError::$foo.
     */
    public function testPropertySyntaxErrorContext()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError', 'foo'));
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->getReader();
        $annots = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\DummyClass2', 'id'));
        $this->assertEquals(3, count($annots));
    }

    public function testNonAnnotationProblem()
    {
        $reader = $this->getReader();

        $this->assertNotNull($annot = $reader->getPropertyAnnotation(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\DummyClassNonAnnotationProblem', 'foo'), $name = 'Doctrine\Tests\Common\Annotations\DummyAnnotation'));
        $this->assertInstanceOf($name, $annot);
    }

    public function testImportWithConcreteAnnotation()
    {
        $reader = $this->getReader();
        $property = new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestImportWithConcreteAnnotation', 'field');
        $annotations = $reader->getPropertyAnnotations($property);
        $this->assertEquals(1, count($annotations));
        $this->assertNotNull($reader->getPropertyAnnotation($property, 'Doctrine\Tests\Common\Annotations\DummyAnnotation'));
    }

    public function testImportWithInheritance()
    {
        $reader = $this->getReader();

        $class = new TestParentClass();
        $ref = new \ReflectionClass($class);

        $childAnnotations = $reader->getPropertyAnnotations($ref->getProperty('child'));
        $this->assertEquals(1, count($childAnnotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Foo\Name', reset($childAnnotations));

        $parentAnnotations = $reader->getPropertyAnnotations($ref->getProperty('parent'));
        $this->assertEquals(1, count($parentAnnotations));
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Bar\Name', reset($parentAnnotations));
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@NameFoo" in property Doctrine\Tests\Common\Annotations\TestAnnotationNotImportedClass::$field was never imported.
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestAnnotationNotImportedClass', 'field'));
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage The annotation "@Foo\Bar\Name" in property Doctrine\Tests\Common\Annotations\TestNonExistentAnnotationClass::$field was never imported.
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        $reader = $this->getReader();
        $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestNonExistentAnnotationClass', 'field'));
    }

    public function testTopLevelAnnotation()
    {
        $reader = $this->getReader();
        $annotations = $reader->getPropertyAnnotations(new \ReflectionProperty('Doctrine\Tests\Common\Annotations\TestTopLevelAnnotationClass', 'field'));

        $this->assertEquals(1, count($annotations));
        $this->assertInstanceOf('\TopLevelAnnotation', reset($annotations));
    }

    public function testIgnoresAnnotationsNotPrefixedWithWhitespace()
    {
        $reader = $this->getReader();

        $annotation = $reader->getClassAnnotation(new \ReflectionClass(new TestIgnoresNonAnnotationsClass()), 'Doctrine\Tests\Common\Annotations\Name');
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\Name', $annotation);
    }

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
 * A description of this class.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 *
 * @author robo
 * @since 2.0
 * @DummyAnnotation(dummyValue="hello")
 */
class DummyClass {
    /**
     * A nice property.
     *
     * @var mixed
     * @DummyAnnotation(dummyValue="fieldHello")
     */
    private $field1;

    /**
     * @DummyJoinTable(name="join_table",
     *      joinColumns={@DummyJoinColumn(name="col1", referencedColumnName="col2")},
     *      inverseJoinColumns={
     *          @DummyJoinColumn(name="col3", referencedColumnName="col4")
     *      })
     */
    private $field2;

    /**
     * Gets the value of field1.
     *
     * @return mixed
     * @DummyAnnotation({1,2,"three"})
     */
    public function getField1() {
    }
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

class DummyId extends \Doctrine\Common\Annotations\Annotation {}
class DummyColumn extends \Doctrine\Common\Annotations\Annotation {
    public $type;
}
class DummyGeneratedValue extends \Doctrine\Common\Annotations\Annotation {}
class DummyAnnotation extends \Doctrine\Common\Annotations\Annotation {
    public $dummyValue;
}
class DummyJoinColumn extends \Doctrine\Common\Annotations\Annotation {
    public $name;
    public $referencedColumnName;
}
class DummyJoinTable extends \Doctrine\Common\Annotations\Annotation {
    public $name;
    public $joinColumns;
    public $inverseJoinColumns;
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

namespace Doctrine\Tests\Common\Annotations\Foo;

class Name extends \Doctrine\Common\Annotations\Annotation
{
    public $name;
}

namespace Doctrine\Tests\Common\Annotations\Bar;

class Name extends \Doctrine\Common\Annotations\Annotation
{
    public $name;
}
