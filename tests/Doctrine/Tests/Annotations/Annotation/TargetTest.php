<?php

namespace Doctrine\Tests\Annotations\Annotation;

use Doctrine\Annotations\Annotation\Target;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \Doctrine\Annotations\Annotation\Target}
 *
 * @covers \Doctrine\Annotations\Annotation\Target
 */
class TargetTest extends TestCase
{
    /**
     * @group DDC-3006
     */
    public function testValidMixedTargets() : void
    {
        $target = new Target(['value' => ['ALL']]);
        self::assertEquals(Target::TARGET_ALL, $target->targets);

        $target = new Target(['value' => ['METHOD', 'METHOD']]);
        self::assertEquals(Target::TARGET_METHOD, $target->targets);
        self::assertNotEquals(Target::TARGET_PROPERTY, $target->targets);

        $target = new Target(['value' => ['PROPERTY', 'METHOD']]);
        self::assertEquals(Target::TARGET_METHOD | Target::TARGET_PROPERTY, $target->targets);
    }
}

