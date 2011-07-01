<?php

namespace Doctrine\Tests\Common\Annotations\Regression;

use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;

use Doctrine\Tests\Common\Annotations\DummyJoinTable;
use Doctrine\Tests\Common\Annotations\DummyId;

/**
 * Important: This class needs a different namespace than Doctrine\Tests\Common\Annotations\
 * to be able to really test the set default annotation namespace functionality.
 */
class BCAnnotationReaderTest extends \Doctrine\Tests\DoctrineTestCase
{
    public function testAnnotations()
    {
        $reader = $this->createAnnotationReader();
    
        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);
        
        $annotName = 'Doctrine\Tests\Common\Annotations\DummyAnnotation';
        $this->assertEquals(1, count($classAnnots));
        $this->assertTrue($classAnnots[$annotName] instanceof \Doctrine\Tests\Common\Annotations\DummyAnnotation); // no use!
        $this->assertEquals("hello", $classAnnots[$annotName]->dummyValue);
        
        $field1Prop = $class->getProperty('field1');
        $propAnnots = $reader->getPropertyAnnotations($field1Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue($propAnnots[$annotName] instanceof \Doctrine\Tests\Common\Annotations\DummyAnnotation); // no use!
        $this->assertEquals("fieldHello", $propAnnots[$annotName]->dummyValue);
        
        $getField1Method = $class->getMethod('getField1');
        $methodAnnots = $reader->getMethodAnnotations($getField1Method);
        $this->assertEquals(1, count($methodAnnots));
        $this->assertTrue($methodAnnots[$annotName] instanceof \Doctrine\Tests\Common\Annotations\DummyAnnotation); // no use!
        $this->assertEquals(array(1, 2, "three"), $methodAnnots[$annotName]->value);
        
        $field2Prop = $class->getProperty('field2');
        $propAnnots = $reader->getPropertyAnnotations($field2Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue(isset($propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable']));
        $joinTableAnnot = $propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable'];
        $this->assertEquals(1, count($joinTableAnnot->joinColumns));
        $this->assertEquals(1, count($joinTableAnnot->inverseJoinColumns));
        $this->assertTrue($joinTableAnnot->joinColumns[0] instanceof \Doctrine\Tests\Common\Annotations\DummyJoinColumn); // no use!
        $this->assertTrue($joinTableAnnot->inverseJoinColumns[0] instanceof \Doctrine\Tests\Common\Annotations\DummyJoinColumn); // no use!
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
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->createAnnotationReader();
        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\Regression\DummyClass2');
        $annotations = $reader->getPropertyAnnotations($class->getProperty('id'));
        $this->assertEquals(3, count($annotations));
    }

    public function testCustomAnnotationCreationFunction()
    {
        $reader = $this->createAnnotationReader();
        $reader->setAnnotationCreationFunction(function($name, $values) {
            if ($name == 'Doctrine\Tests\Common\Annotations\DummyAnnotation') {
                $a = new CustomDummyAnnotationClass;
                $a->setDummyValue($values['dummyValue']);
                return $a;
            }
        });

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);
        $this->assertTrue(isset($classAnnots['Doctrine\Tests\Common\Annotations\Regression\CustomDummyAnnotationClass']));
        $annot = $classAnnots['Doctrine\Tests\Common\Annotations\Regression\CustomDummyAnnotationClass'];
        $this->assertEquals('hello', $annot->getDummyValue());
    }

    public function testNonAnnotationProblem()
    {
        $reader = $this->createAnnotationReader();

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\DummyClassNonAnnotationProblem');
        $annotations = $reader->getPropertyAnnotations($class->getProperty('foo'));
        $this->assertArrayHasKey('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations);
        $this->assertInstanceOf('Doctrine\Tests\Common\Annotations\DummyAnnotation', $annotations['Doctrine\Tests\Common\Annotations\DummyAnnotation']);
    }

    /**
     * @return AnnotationReader
     */
    public function createAnnotationReader()
    {
        $reader = new IndexedReader(new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache));
        $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');
        $reader->setEnableParsePhpImports(false);
        return $reader;
    }
    
    public function testEmailAsAnnotation()
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);
        $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');
        
        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\DummyClassWithEmail');
        $classAnnots = $reader->getClassAnnotations($class);
        
        $this->assertEquals(1, count($classAnnots));
    }
    
    public function testNamespaceAliasedAnnotations()
    {
        $reader = new IndexedReader(new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache));
        $reader->setAnnotationNamespaceAlias('Doctrine\Tests\Common\Annotations\\', 'alias');

        $reflClass = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\AliasNamespace');
        
        $result = $reader->getPropertyAnnotations($reflClass->getProperty('bar'));
        $this->assertEquals(1, count($result));
        $annot = $result['Doctrine\Tests\Common\Annotations\Name'];
        $this->assertTrue($annot instanceof \Doctrine\Tests\Common\Annotations\Name); // no use!
        $this->assertEquals('bar', $annot->foo);
    }

    /**
     * @group DCOM-4
     */
    public function testNamespaceAliasAnnotationWithSeparator()
    {
        $reader = new IndexedReader(new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache));
        $reader->setAnnotationNamespaceAlias('Doctrine\Tests\Common\\', 'alias');

        $reflClass = new ReflectionClass('Doctrine\Tests\Common\Annotations\Regression\AliasNamespace');
        
        $result = $reader->getPropertyAnnotations($reflClass->getProperty('foo'));
        $this->assertEquals(1, count($result));
        $annot = $result['Doctrine\Tests\Common\Annotations\Name'];
        $this->assertTrue($annot instanceof \Doctrine\Tests\Common\Annotations\Name); // no use!
        $this->assertEquals('bar', $annot->foo);
    }
}

class CustomDummyAnnotationClass {
    private $dummyValue;

    public function setDummyValue($value) {
        $this->dummyValue = $value;
    }

    public function getDummyValue() {
        return $this->dummyValue;
    }
}

class AliasNamespace
{

    /**
     * @alias:Name(foo="bar")
     */
    public $bar;
    /**
     * @alias:Annotations\Name(foo="bar")
     */
    public $foo;
}

/**
 * A description of this class.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 * 
 * Copy of the class one namespace up to avoid matches against __NAMESPACE__
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