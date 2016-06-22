<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;

class CachedReaderTest extends AbstractReaderTest
{
    public function testIgnoresStaleCache()
    {
        $name     = 'Doctrine\AnnotationsTests\Fixtures\Controller';
        $class    = new \ReflectionClass($name);
        $file     = $class->getFilename();
        $cacheKey = $name . '@[Annot]';

        touch($file);

        $cache  = $this->getMockBuilder('Doctrine\Common\Cache\Cache')->getMock();
        $reader = new CachedReader(new AnnotationReader(), $cache, true);

        $cache
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($cacheKey))
            ->will($this->returnValue([
                'value' => [],
                'time'  => time() - 10
            ]));

        $cache
            ->expects($this->once())
            ->method('save')
            ->with($this->equalTo($cacheKey), $this->callback(function ($data){

                $this->assertArrayHasKey('value', $data);
                $this->assertArrayHasKey('time', $data);

                $this->assertInternalType('array', $data['value']);
                $this->assertInternalType('integer', $data['time']);

                $this->assertCount(1, $data['value']);
                $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\Route', $data['value'][0]);

                return true;
            }));

        $result = $reader->getClassAnnotations($class);

        $this->assertCount(1, $result);
        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\Route', $result[0]);
    }

    protected function getReader()
    {
        $cache  = new ArrayCache();
        $reader = new CachedReader(new AnnotationReader(), $cache);

        return $reader;
    }
}