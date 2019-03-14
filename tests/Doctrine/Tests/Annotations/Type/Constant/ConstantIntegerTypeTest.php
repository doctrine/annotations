<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\ConstantIntegerType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ConstantIntegerTypeTest extends TestCase
{
    public function testDescribe() : void
    {
        self::assertSame('123', (new ConstantIntegerType(123))->describe());
    }

    public function testValidate() : void
    {
        $type = new ConstantIntegerType(123);

        self::assertTrue($type->validate(123));
        self::assertFalse($type->validate(0));
        self::assertFalse($type->validate(true));
        self::assertFalse($type->validate(null));
        self::assertFalse($type->validate(123.0));
        self::assertFalse($type->validate('abc'));
        self::assertFalse($type->validate([123]));
        self::assertFalse($type->validate(new stdClass()));
    }
}
