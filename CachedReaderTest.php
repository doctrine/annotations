<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Cache\InMemoryCache;
use Doctrine\Common\Annotations\CachedReader;

class CachedReaderTest extends AbstractReaderTest
{
    protected function getReader()
    {
        return new CachedReader(new AnnotationReader(), new InMemoryCache());
    }
}