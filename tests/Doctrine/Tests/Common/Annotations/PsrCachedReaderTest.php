<?php

namespace Doctrine\Tests\Common\Annotations;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassThatUsesTraitThatUsesAnotherTraitWithMethods;
use Doctrine\Tests\Common\Annotations\Fixtures\ClassWithClassAnnotationOnly;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;

use function rawurlencode;
use function time;
use function touch;

final class PsrCachedReaderTest extends AbstractReaderTest
{
    /** @var CacheItemPoolInterface */
    private $cache;

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

        $classReader = new ReflectionClass(PsrCachedReader::class);

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
     * methods' annotations of the same file
     *
     * We test four things
     * 1. we load the file (and its filemtime) for method1's annotation with fresh cache
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
        $cacheTime = time() - 10;

        $cacheKeyMethod1 = rawurlencode($className . '#method1');
        $cacheKeyMethod2 = rawurlencode($className . '#method2');

        $route1          = new Route();
        $route1->pattern = '/someprefix';
        $route2          = new Route();
        $route2->pattern = '/someotherprefix';

        $cacheItem1     = $this->createCacheItem($cacheKeyMethod1, true, [$route1]);
        $timeCacheItem1 = $this->createCacheItem('[C]' . $cacheKeyMethod1, true, $cacheTime);

        $cacheItem2     = $this->createCacheItem($cacheKeyMethod2, true, [$route2]);
        $timeCacheItem2 = $this->createCacheItem('[C]' . $cacheKeyMethod2, true, $cacheTime);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->method('getItem')
            ->willReturnMap([
                [$cacheKeyMethod1, $cacheItem1],
                [$cacheKeyMethod2, $cacheItem2],
                ['[C]' . $cacheKeyMethod1, $timeCacheItem1],
                ['[C]' . $cacheKeyMethod2, $timeCacheItem2],
            ]);
        $cache
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$timeCacheItem2],
                [$cacheItem2]
            );

        $reader = new PsrCachedReader(new AnnotationReader(), $cache, true);

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

    public function testReaderIsNotHitIfCacheIsFresh(): void
    {
        $cache = new ArrayAdapter();

        $readAnnotations = (new PsrCachedReader(new AnnotationReader(), $cache, true))
            ->getClassAnnotations(new ReflectionClass(ClassWithClassAnnotationOnly::class));

        $throwingReader = $this->createMock(Reader::class);
        $throwingReader->expects(self::never())->method(self::anything());

        self::assertEquals(
            $readAnnotations,
            (new PsrCachedReader($throwingReader, $cache, true))
                ->getClassAnnotations(new ReflectionClass(ClassWithClassAnnotationOnly::class))
        );
    }

    protected function doTestCacheStale(string $className, int $lastCacheModification): PsrCachedReader
    {
        $cacheKey = rawurlencode($className);

        $route          = new Route();
        $route->pattern = '/someprefix';

        $cacheItem     = $this->createCacheItem($cacheKey, true, []);
        $timeCacheItem = $this->createCacheItem('[C]' . $cacheKey, true, $lastCacheModification);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->method('getItem')
            ->willReturnMap([
                [$cacheKey, $cacheItem],
                ['[C]' . $cacheKey, $timeCacheItem],
            ]);
        $cache
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$timeCacheItem], [$cacheItem]);

        $reader = new PsrCachedReader(new AnnotationReader(), $cache, true);

        self::assertEquals([$route], $reader->getClassAnnotations(new ReflectionClass($className)));

        return $reader;
    }

    protected function doTestCacheFresh(string $className, int $lastCacheModification): void
    {
        $cacheKey       = rawurlencode($className);
        $route          = new Route();
        $route->pattern = '/someprefix';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem
            ->method('isHit')
            ->willReturn(true);
        $cacheItem
            ->method('get')
            ->willReturn([$route]);

        $timeCacheItem = $this->createMock(CacheItemInterface::class);
        $timeCacheItem
            ->method('isHit')
            ->willReturn(true);
        $timeCacheItem
            ->method('get')
            ->willReturn($lastCacheModification);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache
            ->method('getItem')
            ->willReturnMap([
                [$cacheKey, $cacheItem],
                ['[C]' . $cacheKey, $timeCacheItem],
            ]);
        $cache->expects(self::never())->method('save');
        $cache->expects(self::never())->method('commit');

        $reader = new PsrCachedReader(new AnnotationReader(), $cache, true);

        $this->assertEquals([$route], $reader->getClassAnnotations(new ReflectionClass($className)));
    }

    protected function getReader(): Reader
    {
        $this->cache = new ArrayAdapter();

        return new PsrCachedReader(new AnnotationReader(), $this->cache);
    }

    /** @param mixed $value */
    private function createCacheItem(string $key, bool $isHit, $value = null): CacheItemInterface
    {
        return Closure::bind(
            static function (string $key, $value, bool $isHit): CacheItem {
                $item        = new CacheItem();
                $item->key   = $key;
                $item->value = $value;
                $item->isHit = $isHit;

                return $item;
            },
            null,
            CacheItem::class
        )($key, $value, $isHit);
    }
}
