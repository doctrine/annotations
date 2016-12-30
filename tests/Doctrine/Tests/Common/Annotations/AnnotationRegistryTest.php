<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotationRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $class = AnnotationRegistry::class;

    /**
     * @runInSeparateProcess
     */
    public function testReset()
    {
        $data = array('foo' => 'bar');

        $this->setStaticField($this->class, 'autoloadNamespaces', $data);
        $this->setStaticField($this->class, 'loaders', $data);

        self::assertEquals($data, $this->getStaticField($this->class, 'autoloadNamespaces'));
        self::assertEquals($data, $this->getStaticField($this->class, 'loaders'));

        AnnotationRegistry::reset();

        self::assertEmpty($this->getStaticField($this->class, 'autoloadNamespaces'));
        self::assertEmpty($this->getStaticField($this->class, 'loaders'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterAutoloadNamespaces()
    {
        $this->setStaticField($this->class, 'autoloadNamespaces', array('foo' => 'bar'));

        AnnotationRegistry::registerAutoloadNamespaces(array('test' => 'bar'));
        self::assertEquals(array('foo' => 'bar', 'test' => 'bar'), $this->getStaticField($this->class, 'autoloadNamespaces'));
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