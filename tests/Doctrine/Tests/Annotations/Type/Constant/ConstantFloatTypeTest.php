<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\ConstantFloatType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ConstantFloatTypeTest extends TestCase
{
    public function testGetValue() : void
    {
        self::assertEquals(1.23, (new ConstantFloatType(1.23))->getValue(), '', 1e-5);
        self::assertEquals(1.1111111111, (new ConstantFloatType(1.1111111111))->getValue(), '', 1e-10);
    }

    public function testDescribe() : void
    {
        self::assertSame('1.230000', (new ConstantFloatType(1.23))->describe());
        self::assertSame('1.111111', (new ConstantFloatType(1.1111111111))->describe());
    }

    public function testValidate() : void
    {
        $type = new ConstantFloatType(1.1111111111);

        self::assertTrue($type->validate(1.1111111111));
        self::assertTrue($type->validate(1.1111111111000000000000000000000000001));
        self::assertFalse($type->validate(1.11111));
        self::assertFalse($type->validate('1.1111111111'));
        self::assertFalse($type->validate(1.23));
        self::assertFalse($type->validate(null));
        self::assertFalse($type->validate(123));
        self::assertFalse($type->validate('abc'));
        self::assertFalse($type->validate([123]));
        self::assertFalse($type->validate(new stdClass()));
    }
}
