<?php

namespace Doctrine\Tests\Common\Annotations\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \Doctrine\Common\Annotations\Annotation\Target}
 *
 * @covers \Doctrine\Common\Annotations\Annotation\Target
 */
class TargetTest extends TestCase
{
    /**
     * @group DDC-3006
     */
    public function testValidMixedTargets(): void
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
