<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotationRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $class = 'Doctrine\Common\Annotations\AnnotationRegistry';

    /**
     * @runInSeparateProcess
     */
    public function testReset()
    {
        $data = array('foo' => 'bar');

        $this->setStaticField($this->class, 'autoloadNamespaces', $data);
        $this->setStaticField($this->class, 'loaders', $data);

        $this->assertEquals($data, $this->getStaticField($this->class, 'autoloadNamespaces'));
        $this->assertEquals($data, $this->getStaticField($this->class, 'loaders'));

        AnnotationRegistry::reset();

        $this->assertEmpty($this->getStaticField($this->class, 'autoloadNamespaces'));
        $this->assertEmpty($this->getStaticField($this->class, 'loaders'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterAutoloadNamespaces()
    {
        $this->setStaticField($this->class, 'autoloadNamespaces', array('foo' => 'bar'));

        AnnotationRegistry::registerAutoloadNamespaces(array('test' => 'bar'));
        $this->assertEquals(array('foo' => 'bar', 'test' => 'bar'), $this->getStaticField($this->class, 'autoloadNamespaces'));
    }

    /**
     * @runInSeparateProcess
     *
     * @expectedException   \InvalidArgumentException
     * @expectedExceptionMessage A callable is expected in AnnotationRegistry::registerLoader().
     */
    public function testRegisterLoaderNoCallable()
    {
        AnnotationRegistry::registerLoader('test');
    }

    protected function setStaticField($class, $field, $value)
    {
        $reflection = new \ReflectionProperty($class, $field);

        $reflection->setAccessible(true);
        $reflection->setValue(null, $value);
    }

    protected function getStaticField($class, $field)
    {
        $reflection = new \ReflectionProperty($class, $field);

        $reflection->setAccessible(true);

        return $reflection->getValue();
    }
}