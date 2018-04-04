<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;

class AnnotationRegistryTest extends TestCase
{
    protected $class = AnnotationRegistry::class;

    /**
     * @runInSeparateProcess
     */
    public function testReset() : void
    {
        $data = ['foo' => 'bar'];

        $this->setStaticField($this->class, 'autoloadNamespaces', $data);
        $this->setStaticField($this->class, 'loaders', $data);

        self::assertSame($data, $this->getStaticField($this->class, 'autoloadNamespaces'));
        self::assertSame($data, $this->getStaticField($this->class, 'loaders'));

        AnnotationRegistry::reset();

        self::assertEmpty($this->getStaticField($this->class, 'autoloadNamespaces'));
        self::assertEmpty($this->getStaticField($this->class, 'loaders'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterAutoloadNamespaces() : void
    {
        $this->setStaticField($this->class, 'autoloadNamespaces', ['foo' => 'bar']);

        AnnotationRegistry::registerAutoloadNamespaces(['test' => 'bar']);
        self::assertSame(['foo' => 'bar', 'test' => 'bar'], $this->getStaticField($this->class, 'autoloadNamespaces'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegisterLoaderNoCallable() : void
    {
        $this->expectException(\TypeError::class);

        AnnotationRegistry::registerLoader('test' . random_int(10, 10000));
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
        $autoLoader = function () use (&$i) : bool {
            $i += 1;
            return false;
        };
        AnnotationRegistry::registerLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        AnnotationRegistry::loadAnnotationClass('unloadableClass');
        self::assertSame(1, $i, 'Autoloader should only be called once');
    }

    /**
     * @runInSeparateProcess
     */
    public function testStopCallingLoadersAfterClassIsFound() : void
    {
        $className = 'autoloadedClass' . random_int(10, 100000);
        AnnotationRegistry::reset();
        $i = 0;
        $autoLoader = function () use (&$i, $className) : bool {
            eval('class ' . $className . ' {}');
            $i += 1;
            return true;
        };
        AnnotationRegistry::registerLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass($className);
        AnnotationRegistry::loadAnnotationClass($className);
        AnnotationRegistry::loadAnnotationClass($className);
        self::assertSame(1, $i, 'Autoloader should only be called once');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddingANewLoaderClearsTheCache() : void
    {
        $failures         = 0;
        $failingLoader    = function () use (& $failures) : bool {
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

        AnnotationRegistry::registerLoader(function () : bool {
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
        $failingLoader    = function () use (& $failures) : bool {
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

    /**
     * @runInSeparateProcess
     */
    public function testRegisterLoaderIfNotExistsOnlyRegisteresSameLoaderOnce() : void
    {
        $className = 'autoloadedClassThatDoesNotExist';
        AnnotationRegistry::reset();
        $autoLoader = self::createPartialMock(\stdClass::class, ['__invoke']);
        $autoLoader->expects($this->once())->method('__invoke');
        AnnotationRegistry::registerUniqueLoader($autoLoader);
        AnnotationRegistry::registerUniqueLoader($autoLoader);
        AnnotationRegistry::loadAnnotationClass($className);
        AnnotationRegistry::loadAnnotationClass($className);
    }
}
