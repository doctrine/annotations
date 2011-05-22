<?php

namespace Doctrine\Tests\Common\Annotations\Regression;

use Doctrine\Tests\Common\Annotations\DummyAnnotation;
use Doctrine\Tests\Common\Annotations\DummyJoinColumn;
use Doctrine\Tests\Common\Annotations\Name;
use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;

/**
 * Important: This class needs a different namespace than Doctrine\Tests\Common\Annotations\
 * to be able to really test the set default annotation namespace functionality.
 */
class BCAnnotationReaderTest extends \Doctrine\Tests\DoctrineTestCase
{
    public function testAnnotations()
    {
        $reader = $this->createAnnotationReader();
        
        $this->assertFalse($reader->getAutoloadAnnotations());
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->getAutoloadAnnotations());
        $reader->setAutoloadAnnotations(false);
        $this->assertFalse($reader->getAutoloadAnnotations());
    
        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);
        
        $annotName = 'Doctrine\Tests\Common\Annotations\DummyAnnotation';
        $this->assertEquals(1, count($classAnnots));
        $this->assertTrue($classAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals("hello", $classAnnots[$annotName]->dummyValue);
        
        $field1Prop = $class->getProperty('field1');
        $propAnnots = $reader->getPropertyAnnotations($field1Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue($propAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals("fieldHello", $propAnnots[$annotName]->dummyValue);
        
        $getField1Method = $class->getMethod('getField1');
        $methodAnnots = $reader->getMethodAnnotations($getField1Method);
        $this->assertEquals(1, count($methodAnnots));
        $this->assertTrue($methodAnnots[$annotName] instanceof DummyAnnotation);
        $this->assertEquals(array(1, 2, "three"), $methodAnnots[$annotName]->value);
        
        $field2Prop = $class->getProperty('field2');
        $propAnnots = $reader->getPropertyAnnotations($field2Prop);
        $this->assertEquals(1, count($propAnnots));
        $this->assertTrue(isset($propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable']));
        $joinTableAnnot = $propAnnots['Doctrine\Tests\Common\Annotations\DummyJoinTable'];
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

    public function testClassSyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected namespace separator or identifier, got ')' at position 18 in class Doctrine\Tests\Common\Annotations\DummyClassSyntaxError."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassSyntaxError');

        $reader = $this->createAnnotationReader();
        $reader->getClassAnnotations($class);
    }

    public function testMethodSyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected namespace separator or identifier, got ')' at position 18 in method Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError::foo()."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassMethodSyntaxError');
        $method = $class->getMethod('foo');

        $reader = $this->createAnnotationReader();
        $reader->getMethodAnnotations($method);
    }

    public function testPropertySyntaxErrorContext()
    {
        $this->setExpectedException(
            "Doctrine\Common\Annotations\AnnotationException",
            "[Syntax Error] Expected namespace separator or identifier, got ')' at position 18 in property Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError::\$foo."
        );

        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClassPropertySyntaxError');
        $property = $class->getProperty('foo');

        $reader = $this->createAnnotationReader();
        $reader->getPropertyAnnotations($property);
    }

    /**
     * @group regression
     */
    public function testMultipleAnnotationsOnSameLine()
    {
        $reader = $this->createAnnotationReader();
        $class = new ReflectionClass('\Doctrine\Tests\Common\Annotations\DummyClass2');
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

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClass');
        $classAnnots = $reader->getClassAnnotations($class);
        $this->assertTrue(isset($classAnnots['Doctrine\Tests\Common\Annotations\Regression\CustomDummyAnnotationClass']));
        $annot = $classAnnots['Doctrine\Tests\Common\Annotations\Regression\CustomDummyAnnotationClass'];
        $this->assertEquals('hello', $annot->getDummyValue());
    }

    public function testNonAnnotationProblem()
    {
        $reader = $this->createAnnotationReader();

        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClassNonAnnotationProblem');
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

    /**
     * @group DCOM-25
     */
    public function testSetAutoloadAnnotations()
    {
        $reader = $this->createAnnotationReader();
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->getAutoloadAnnotations());
    }
    
    public function testEmailAsAnnotation()
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);
        $reader->setDefaultAnnotationNamespace('Doctrine\Tests\Common\Annotations\\');
        
        $class = new ReflectionClass('Doctrine\Tests\Common\Annotations\DummyClassWithEmail');
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
        $this->assertTrue($annot instanceof Name);
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
        $this->assertTrue($annot instanceof Name);
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