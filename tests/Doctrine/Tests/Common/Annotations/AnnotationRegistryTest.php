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
        self::assertEmpty($this->getStaticField($this->class, 'loaded'));
        self::assertEmpty($this->getStaticField($this->class, 'unloadable'));
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

    public function testStopCallingLoadersIfClassIsNotFound()
    {
        AnnotationRegistry::reset();
        $i = 0;
        $autoLoader = function($annotation) use (&$i) {
            $i++;
            return false;
        };
        AnnotationRegistry::registerLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        $this->assertEquals(1, $i, 'Autoloader should only be called once');
    }

    public function testStopCallingLoadersAfterClassIsFound()
    {
        AnnotationRegistry::reset();
        $i = 0;
        $autoLoader = function($annotation) use (&$i) {
            $i++;
            return true;
        };
        AnnotationRegistry::registerLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass(self::class);
        AnnotationRegistry::loadAnnotationClass(self::class);
        AnnotationRegistry::loadAnnotationClass(self::class);
        $this->assertEquals(1, $i, 'Autoloader should only be called once');
    }

    public function testAddingANewLoaderClearsTheCache()
    {
        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader('class_exists');
        AnnotationRegistry::loadAnnotationClass(self::class);
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        AnnotationRegistry::registerLoader('class_exists');
        self::assertEmpty($this->getStaticField($this->class, 'loaded'));
        self::assertEmpty($this->getStaticField($this->class, 'unloadable'));
    }
}