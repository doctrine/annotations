<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassThatUsesTraitThatUsesAnotherTraitWithMethods;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function class_exists;
use function time;
use function touch;

class CachedReaderTest extends AbstractReaderTest
{
    /** @var int|ArrayCache */
    private $cache;

    protected function setUp(): void
    {
        if (! class_exists(ArrayCache::class)) {
            $this->markTestSkipped('Cannot test deprecated cached reader without doctrine/cache 1.x');
        }

        parent::setup();
    }

    public function testIgnoresStaleCache(): void
    {
        $cache = time() - 10;
        touch(__DIR__ . '/Fixtures/Controller.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\Controller::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithParentClass(): void
    {
        $cache = time() - 10;
        touch(__DIR__ . '/Fixtures/ControllerWithParentClass.php', $cache - 10);
        touch(__DIR__ . '/Fixtures/AbstractController.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\ControllerWithParentClass::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithTraits(): void
    {
        $cache = time() - 10;
        touch(__DIR__ . '/Fixtures/ControllerWithTrait.php', $cache - 10);
        touch(__DIR__ . '/Fixtures/Traits/SecretRouteTrait.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\ControllerWithTrait::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithTraitsThatUseOtherTraits(): void
    {
        $cache = time() - 10;

        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTrait.php', $cache - 10);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cache + 10);

        $this->doTestCacheStale(
            Fixtures\ClassThatUsesTraitThatUsesAnotherTrait::class,
            $cache
        );
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithInterfacesThatExtendOtherInterfaces(): void
    {
        $cache = time() - 10;

        touch(__DIR__ . '/Fixtures/InterfaceThatExtendsAnInterface.php', $cache - 10);
        touch(__DIR__ . '/Fixtures/EmptyInterface.php', $cache + 10);

        $this->doTestCacheStale(
            Fixtures\InterfaceThatExtendsAnInterface::class,
            $cache
        );
    }

    /**
     * @group 62
     * @group 105
     */
    public function testUsesFreshCacheWithTraitsThatUseOtherTraits(): void
    {
        $cacheTime = time();

        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTrait.php', $cacheTime - 10);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cacheTime - 10);

        $this->doTestCacheFresh(
            'Doctrine\Tests\Common\Annotations\Fixtures\ClassThatUsesTraitThatUsesAnotherTrait',
            $cacheTime
        );
    }

    /**
     * @group 62
     */
    public function testPurgeLoadedAnnotations(): void
    {
        $cache = time() - 10;

        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTrait.php', $cache - 10);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cache + 10);

        $reader = $this->doTestCacheStale(
            Fixtures\ClassThatUsesTraitThatUsesAnotherTrait::class,
            $cache
        );

        $classReader = new ReflectionClass(CachedReader::class);

        $loadedAnnotationsProperty = $classReader->getProperty('loadedAnnotations');
        $loadedAnnotationsProperty->setAccessible(true);
        $this->assertCount(1, $loadedAnnotationsProperty->getValue($reader));

        $loadedFilemtimesProperty = $classReader->getProperty('loadedFilemtimes');
        $loadedFilemtimesProperty->setAccessible(true);
        $this->assertCount(3, $loadedFilemtimesProperty->getValue($reader));

        $reader->clearLoadedAnnotations();

        $this->assertCount(0, $loadedAnnotationsProperty->getValue($reader));
        $this->assertCount(0, $loadedFilemtimesProperty->getValue($reader));
    }

    /**
     * As there is a cache on loadedAnnotations, we need to test two different
     * method's annotations of the same file
     *
     * We test four things
     * 1. we load the file (and its filemtime) for method1 annotation with fresh cache
     * 2. we load the file for method2 with stale cache => but still no save, because seen as fresh
     * 3. we purge loaded annotations and filemtime
     * 4. same as 2, but this time without filemtime cache, so file seen as stale and new cache is saved
     *
     * @group 62
     * @group 105
     */
    public function testAvoidCallingFilemtimeTooMuch(): void
    {
        $this->markTestSkipped('Skipped until further investigation');

        $className = ClassThatUsesTraitThatUsesAnotherTraitWithMethods::class;
        $cacheKey  = $className;
        $cacheTime = time() - 10;

        $cacheKeyMethod1 = $cacheKey . '#method1';
        $cacheKeyMethod2 = $cacheKey . '#method2';

        $route1          = new Route();
        $route1->pattern = '/someprefix';
        $route2          = new Route();
        $route2->pattern = '/someotherprefix';

        $cache = $this->createMock('Doctrine\Common\Cache\Cache');
        assert($cache instanceof Cache && $cache instanceof MockObject);

        $cache
            ->expects($this->exactly(6))
            ->method('fetch')
            ->withConsecutive(
                // first pass => cache ok for method 1
                // we load annotations AND filemtimes for this file
                [$this->equalTo($cacheKeyMethod1)],
                [$this->equalTo('[C]' . $cacheKeyMethod1)],
                // second pass => cache ok for method 2
                // filemtime is seen as fresh even if it's not
                [$this->equalTo($cacheKeyMethod2)],
                [$this->equalTo('[C]' . $cacheKeyMethod2)],
                // third pass => cache stale for method 2
                // filemtime is seen as not fresh => we save
                [$this->equalTo($cacheKeyMethod2)],
                [$this->equalTo('[C]' . $cacheKeyMethod2)]
            )
            ->willReturnOnConsecutiveCalls(
                [$route1], // Result was cached, but there was an annotation;
                $cacheTime,
                [$route2], // Result was cached, but there was an annotation;
                $cacheTime,
                [$route2], // Result was cached, but there was an annotation;
                $cacheTime
            );

        $cache
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$this->equalTo($cacheKeyMethod2)],
                [$this->equalTo('[C]' . $cacheKeyMethod2)]
            );

        $reader = new CachedReader(new AnnotationReader(), $cache, true);

        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTraitWithMethods.php', $cacheTime - 20);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cacheTime - 20);
        $this->assertEquals([$route1], $reader->getMethodAnnotations(new ReflectionMethod($className, 'method1')));

        // only filemtime changes, but not cleared => no change
        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTrait.php', $cacheTime + 5);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cacheTime + 5);
        $this->assertEquals([$route2], $reader->getMethodAnnotations(new ReflectionMethod($className, 'method2')));

        $reader->clearLoadedAnnotations();
        $this->assertEquals([$route2], $reader->getMethodAnnotations(new ReflectionMethod($className, 'method2')));
    }

    protected function doTestCacheStale(string $className, int $lastCacheModification): CachedReader
    {
        $cacheKey = $className;

        $cache = $this->createMock(Cache::class);
        $cache
            ->expects($this->exactly(2))
            ->method('fetch')
            ->withConsecutive(
                [$this->equalTo($cacheKey)],
                [$this->equalTo('[C]' . $cacheKey)]
            )
            ->willReturnOnConsecutiveCalls(
                [], // Result was cached, but there was no annotation
                $lastCacheModification
            );
        $cache
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$this->equalTo($cacheKey)],
                [$this->equalTo('[C]' . $cacheKey)]
            );

        $reader         = new CachedReader(new AnnotationReader(), $cache, true);
        $route          = new Route();
        $route->pattern = '/someprefix';

        self::assertEquals([$route], $reader->getClassAnnotations(new ReflectionClass($className)));

        return $reader;
    }

    protected function doTestCacheFresh(string $className, int $lastCacheModification): void
    {
        $cacheKey       = $className;
        $route          = new Route();
        $route->pattern = '/someprefix';

        $cache = $this->createMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->exactly(2))
            ->method('fetch')
            ->withConsecutive(
                [$this->equalTo($cacheKey)],
                [$this->equalTo('[C]' . $cacheKey)]
            )
            ->willReturnOnConsecutiveCalls(
                [$route],
                $lastCacheModification
            );
        $cache->expects(self::never())->method('save');

        $reader = new CachedReader(new AnnotationReader(), $cache, true);

        $this->assertEquals([$route], $reader->getClassAnnotations(new ReflectionClass($className)));
    }

    protected function getReader(): Reader
    {
        $this->cache = new ArrayCache();

        return new CachedReader(new AnnotationReader(), $this->cache);
    }
}
