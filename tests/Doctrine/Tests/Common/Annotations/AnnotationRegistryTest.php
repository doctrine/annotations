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

    /**
     * @runInSeparateProcess
     */
    public function testStopCallingLoadersIfClassIsNotFound() : void
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

    /**
     * @runInSeparateProcess
     */
    public function testStopCallingLoadersAfterClassIsFound() : void
    {
        $className = 'autoloadedClass' . random_int(10, 100000);
        AnnotationRegistry::reset();
        $i = 0;
        $autoLoader = function($annotation) use (&$i, $className) {
            eval('class ' . $className . ' {}');
            $i++;
            return true;
        };
        AnnotationRegistry::registerLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass($className);
        AnnotationRegistry::loadAnnotationClass($className);
        AnnotationRegistry::loadAnnotationClass($className);
        $this->assertEquals(1, $i, 'Autoloader should only be called once');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddingANewLoaderClearsTheCache() : void
    {
        $failures         = 0;
        $failingLoader    = function (string $annotation) use (& $failures) : bool {
            $failures += 1;

            return false;
        };

        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader($failingLoader);

        self::assertSame(0, $failures);

        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(1, $failures);

        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(1, $failures);

        AnnotationRegistry::registerLoader(function () {
            return false;
        });
        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(2, $failures);
    }

    /**
     * @runInSeparateProcess
     */
    public function testResetClearsRegisteredAutoloaderFailures() : void
    {
        $failures         = 0;
        $failingLoader    = function (string $annotation) use (& $failures) : bool {
            $failures += 1;

            return false;
        };

        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader($failingLoader);

        self::assertSame(0, $failures);

        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(1, $failures);

        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(1, $failures);

        AnnotationRegistry::reset();
        AnnotationRegistry::registerLoader($failingLoader);
        AnnotationRegistry::loadAnnotationClass('unloadableClass');

        self::assertSame(2, $failures);
    }
}