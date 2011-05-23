<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;

class CachedReaderTest extends AbstractReaderTest
{
    private $cache;
    
    protected function getReader()
    {
        $this->cache = new ArrayCache();
        return new CachedReader(new AnnotationReader(), $this->cache);
    }
}