<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;

class CachedReaderTest extends AbstractReaderTest
{
    private $cache;

    public function testIgnoresStaleCache()
    {
        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/Controller.php', $cache + 10);

        $this->doTestCacheStale('Doctrine\Tests\Common\Annotations\Fixtures\Controller', $cache);
    }

    public function testIgnoresStaleCacheWithParentClass()
    {
        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/ControllerWithParentClass.php', $cache - 10);
        touch(__DIR__.'/Fixtures/AbstractController.php', $cache + 10);

        $this->doTestCacheStale('Doctrine\Tests\Common\Annotations\Fixtures\ControllerWithParentClass', $cache);
    }

    public function testIgnoresStaleCacheWithTraits()
    {
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            $this->markTestSkipped('This test needs PHP >= 5.4');
        }

        $cache = time() - 10;
        touch(__DIR__.'/Fixtures/ControllerWithTrait.php', $cache - 10);
        touch(__DIR__.'/Fixtures/Traits/SecretRouteTrait.php', $cache + 10);

        $this->doTestCacheStale('Doctrine\Tests\Common\Annotations\Fixtures\ControllerWithTrait', $cache);
    }

    protected function doTestCacheStale($className, $lastCacheModification)
    {
        $cacheKey = $className.'@[Annot]';

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
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

        $reader = new CachedReader(new AnnotationReader(), $cache, true);
        $route = new Route();
        $route->pattern = '/someprefix';

        $this->assertEquals(array($route), $reader->getClassAnnotations(new \ReflectionClass($className)));
    }

    protected function getReader()
    {
        $this->cache = new ArrayCache();
        return new CachedReader(new AnnotationReader(), $this->cache);
    }
}