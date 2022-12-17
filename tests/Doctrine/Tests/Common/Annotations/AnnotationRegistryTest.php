<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\CanBeAutoLoaded;
use PHPUnit\Framework\TestCase;

class AnnotationRegistryTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testClassExistsFallback(): void
    {
        AnnotationRegistry::reset();

        self::assertTrue(AnnotationRegistry::loadAnnotationClass(CanBeAutoLoaded::class));
    }
}
