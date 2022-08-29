<?php

namespace Doctrine\Tests\Common\Annotations\Ticket;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Tests\Common\Annotations\DummyTable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group GH-8294
 */
class GH8294Test extends TestCase
{
    /**
     * @group GH-8294
     */
    public function testPersitingEntityWithInvalidTableName()
    {
        $reader = new AnnotationReader();
        $result = $reader->getClassAnnotation(new ReflectionClass(new GH8294Entity()), DummyTable::class);
        self::assertSame($result->name, 'GH8294_entity');
    }
}
