<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Cache\Cache;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;

class CachedReaderTest extends AbstractReaderTest
{
    private $cache;

    public function testIgnoresStaleCache() :void
    {
        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/Controller.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\Controller::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithParentClass() :void
    {
        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/ControllerWithParentClass.php', $cache - 10);
        touch(__DIR__.'/Fixtures/AbstractController.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\ControllerWithParentClass::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithTraits() :void
    {
        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/ControllerWithTrait.php', $cache - 10);
        touch(__DIR__.'/Fixtures/Traits/SecretRouteTrait.php', $cache + 10);

        $this->doTestCacheStale(Fixtures\ControllerWithTrait::class, $cache);
    }

    /**
     * @group 62
     */
    public function testIgnoresStaleCacheWithTraitsThatUseOtherTraits() :void
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
    public function testIgnoresStaleCacheWithInterfacesThatExtendOtherInterfaces() :void
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
    public function testUsesFreshCacheWithTraitsThatUseOtherTraits() :void
    {
        $cacheTime = time();

        touch(__DIR__ . '/Fixtures/ClassThatUsesTraitThatUsesAnotherTrait.php', $cacheTime - 10);
        touch(__DIR__ . '/Fixtures/Traits/EmptyTrait.php', $cacheTime - 10);

        $this->doTestCacheFresh(
            'Doctrine\Tests\Common\Annotations\Fixtures\ClassThatUsesTraitThatUsesAnotherTrait',
            $cacheTime
        );
    }

    protected function doTestCacheStale($className, $lastCacheModification) :void
    {
        $cacheKey = $className;

        /* @var $cache Cache|\PHPUnit_Framework_MockObject_MockObject */
        $cache = $this->createMock(Cache::class);
        $cache
            ->expects($this->at(0))
            ->method('fetch')
            ->with($this->equalTo($cacheKey))
            ->will($this->returnValue(array())) // Result was cached, but there was no annotation
        ;
        $cache
            ->expects($this->at(1))
            ->method('fetch')
            ->with($this->equalTo('[C]'.$cacheKey))
            ->will($this->returnValue($lastCacheModification))
        ;
        $cache
            ->expects($this->at(2))
            ->method('save')
            ->with($this->equalTo($cacheKey))
        ;
        $cache
            ->expects($this->at(3))
            ->method('save')
            ->with($this->equalTo('[C]'.$cacheKey))
        ;

        $cacheReader = new CachedReader(new AnnotationReader(), $cache, true);
        $route = new Route();
        $route->pattern = '/someprefix';

        self::assertEquals(array($route), $cacheReader->getClassAnnotations(new \ReflectionClass($className)));
    }

    protected function doTestCacheFresh($className, $lastCacheModification) :void
    {
        $cacheKey       = $className;
        $route          = new Route();
        $route->pattern = '/someprefix';

        /* @var $cache Cache|\PHPUnit_Framework_MockObject_MockObject */
        $cache = $this->createMock(Cache::class);
        $cache
            ->expects($this->at(0))
            ->method('fetch')
            ->with($this->equalTo($cacheKey))
            ->willReturn(array($route)); // Result was cached, but there was an annotation;
        $cache
            ->expects($this->at(1))
            ->method('fetch')
            ->with($this->equalTo('[C]' . $cacheKey))
            ->willReturn($lastCacheModification);
        $cache->expects(self::never())->method('save');

        $cacheReader = new CachedReader(new AnnotationReader(), $cache, true);

        $this->assertEquals(array($route), $cacheReader->getClassAnnotations(new \ReflectionClass($className)));

        $cache->expects(self::never())->method('fetch');
        $cacheReader->getClassAnnotations(new \ReflectionClass($className));
    }

    protected function getReader() :CachedReader
    {
        $this->cache = new ArrayCache();
        return new CachedReader(new AnnotationReader(), $this->cache);
    }
}
