<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\NullType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NullTypeTest extends TestCase
{
    public function testDescribe() : void
    {
        self::assertSame('null', (new NullType())->describe());
    }

    public function testValidate() : void
    {
        $type = new NullType();

        self::assertTrue($type->validate(null));
        self::assertFalse($type->validate(true));
        self::assertFalse($type->validate(123));
        self::assertFalse($type->validate(1.23));
        self::assertFalse($type->validate('abc'));
        self::assertFalse($type->validate([123]));
        self::assertFalse($type->validate(new stdClass()));
    }
}
