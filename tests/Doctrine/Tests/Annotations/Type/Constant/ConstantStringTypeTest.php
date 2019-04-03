<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type\Constant;

use Doctrine\Annotations\Type\Constant\ConstantStringType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ConstantStringTypeTest extends TestCase
{
    public function testDescribe() : void
    {
        self::assertSame('""', (new ConstantStringType(''))->describe());
        self::assertSame('"Hello world"', (new ConstantStringType('Hello world'))->describe());
        self::assertSame('"Hello \"world\""', (new ConstantStringType('Hello "world"'))->describe());
        self::assertSame('"\\\\Hello \\\\world"', (new ConstantStringType('\Hello \world'))->describe());
    }

    public function testValidate() : void
    {
        $type = new ConstantStringType('Hello world');

        self::assertTrue($type->validate('Hello world'));
        self::assertFalse($type->validate('Hello world '));
        self::assertFalse($type->validate(''));
        self::assertFalse($type->validate(null));
        self::assertFalse($type->validate(true));
        self::assertFalse($type->validate(123));
        self::assertFalse($type->validate(1.23));
        self::assertFalse($type->validate('abc'));
        self::assertFalse($type->validate([123]));
        self::assertFalse($type->validate(new stdClass()));
    }
}
