<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationFactory;


class AnnotationFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @group AnnotationFactory
     */
    public function testNewAnnotationWithoutConstructor()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassName';
        $factory    = new AnnotationFactory();
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof SomeAnnotationClassName);
        
        $this->assertNull($annot->name);
        $this->assertNotNull($annot->data);
        $this->assertEquals($annot->data, "Some data");
        
        
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data","name"=>"Some Name"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof SomeAnnotationClassName);
        
        $this->assertEquals($annot->name, "Some Name");
        $this->assertEquals($annot->data, "Some data");
        
        
        $class      = __NAMESPACE__.'\MarkedClassName';
        $annot      = $factory->newAnnotation($class,array("foo"=>"Foo Val","name"=>"Some Name"));
        
        $this->assertNotNull($annot);
        $this->assertInstanceOf($class, $annot);
        
        $this->assertEquals($annot->foo, "Foo Val");
        $this->assertEquals($annot->name, "Some Name");
        
    }
    
     
    /**
     * @group AnnotationFactory
     */
    public function testNewAnnotationWithConstructor()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassNameWithConstructor';
        $factory    = new AnnotationFactory();
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof SomeAnnotationClassNameWithConstructor);
        $this->assertNull($annot->name);
        $this->assertNotNull($annot->data);
        $this->assertEquals($annot->data, "Some data");
        
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data","name"=>"Some Name"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof SomeAnnotationClassNameWithConstructor);
        
        $this->assertEquals($annot->name, "Some Name");
        $this->assertEquals($annot->data, "Some data");

    }
    
    /**
     * @group AnnotationFactory
     */
    public function testValues()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassName';
        $factory    = new AnnotationFactory();
        
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
        $this->assertEquals($annot->data, "Some data");
        $this->assertNull($annot->name);
        
        
        $annot      = $factory->newAnnotation($class,array("name"=>"Some name"));
        $this->assertEquals($annot->name, "Some name");
        $this->assertNull($annot->data);
       
        
        $annot      = $factory->newAnnotation($class,array("value"=>"Some data"));
        $this->assertEquals($annot->data, "Some data");
        $this->assertNull($annot->name);
        
        
        $annot      = $factory->newAnnotation($class,array("name"=>"Some name",'data'=>"Some data"));
        $this->assertEquals($annot->name, "Some name");
        $this->assertEquals($annot->data, "Some data");
        
       
    }
    
    
    /**
     * @group AnnotationFactory
     * @expectedException \BadMethodCallException
     */
    public function testSetValuesExeption()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassName';
        $factory    = new AnnotationFactory();
        
        $annot      = $factory->newAnnotation($class, array("invalidaProperty"=>"Some value"));
    }
    
    
    /**
     * @group AnnotationFactory
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The constructor of class Doctrine\Tests\Common\Annotations\SomeAnnotationClassNameWithConstructorNotArray must be public and type of first parameter should be an array.
     */
    public function testNewAnnotationWithConstructorNotArray()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassNameWithConstructorNotArray';
        $factory    = new AnnotationFactory();
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
    }
    
     /**
     * @group AnnotationFactory
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The constructor of class Doctrine\Tests\Common\Annotations\SomeAnnotationClassNameWithConstructorPrivate must be public and type of first parameter should be an array.
     */
    public function testNewAnnotationWithConstructorPrivate()
    {
        $class      = __NAMESPACE__.'\SomeAnnotationClassNameWithConstructorPrivate';
        $factory    = new AnnotationFactory();
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
    }
     
     /**
     * @group AnnotationFactory
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class InvalidClassName does not exist.
     */
    public function testInvalidClassName()
    {
        $class      = "InvalidClassName";
        $factory    = new AnnotationFactory();
        $annot      = $factory->newAnnotation($class,array("data"=>"Some data"));
    }
    
    
}

/**
 * @AnnotationTargetClass("Some data")
 */
class MarkedClassName
{

    /**
     * @MarkedAnnotation("Some data")
     */
    public $foo;
    
    
    /**
     * @MarkedAnnotation("Some data",name="Some name")
     */
    public $name;
    
    /**
     * @MarkedAnnotation("Some data",name="Some name")
     */
    public function someFunction()
    {
        
    }

}

class SomeAnnotationClassName
{
    
    private $data;
    
    private $name;
    
    public function __get($name)
    {
        if(isset($this->$name)){
            return $this->$name;
        }
        return null;
    }
}

class SomeAnnotationClassNameWithConstructor 
{
    
    public function __construct(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }

    private $data;
    
    private $name;
    
    public function __get($name)
    {
        if(isset($this->$name)){
            return $this->$name;
        }
        return null;
    }
}


class SomeAnnotationClassNameWithConstructorNotArray
{
    public function __construct(\stdClass $arg)
    {

    }
}

class SomeAnnotationClassNameWithConstructorPrivate
{
    private function __construct(\stdClass $arg)
    {

    }
}