<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\ConstantBooleanType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ConstantBooleanTypeTest extends TestCase
{
    public function testGetValue() : void
    {
        self::assertTrue((new ConstantBooleanType(true))->getValue());
        self::assertFalse((new ConstantBooleanType(false))->getValue());
    }

    public function testDescribe() : void
    {
        self::assertSame('true', (new ConstantBooleanType(true))->describe());
        self::assertSame('false', (new ConstantBooleanType(false))->describe());
    }

    public function testValidate() : void
    {
        $type = new ConstantBooleanType(true);

        self::assertTrue($type->validate(true));
        self::assertFalse($type->validate(false));
        self::assertFalse($type->validate(null));
        self::assertFalse($type->validate(123));
        self::assertFalse($type->validate(1.23));
        self::assertFalse($type->validate('abc'));
        self::assertFalse($type->validate([123]));
        self::assertFalse($type->validate(new stdClass()));
    }
}
