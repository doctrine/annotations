<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Type;

use Doctrine\Annotations\Type\Type;
use PHPUnit\Framework\TestCase;

abstract class TypeTest extends TestCase
{
    /** @var Type */
    private $type;

    final protected function setUp() : void
    {
        parent::setUp();

        $this->type = $this->createType();
    }

    abstract protected function createType() : Type;

    final protected function getType() : Type
    {
        return $this->type;
    }

    public function testDescribe() : void
    {
        self::assertSame($this->getDescription(), $this->type->describe());
    }

    abstract public function getDescription() : string;

    /**
     * @param mixed $value
     *
     * @dataProvider validValidateValuesProvider()
     */
    public function testValidValidateValues($value) : void
    {
        self::assertTrue($this->type->validate($value));
    }

    /**
     * @return mixed[][]
     */
    abstract public function validValidateValuesProvider() : iterable;

    /**
     * @param mixed $value
     *
     * @dataProvider invalidValidateValuesProvider()
     */
    public function testInvalidValidateValues($value) : void
    {
        self::assertFalse($this->type->validate($value));
    }

    /**
     * @return mixed[][]
     */
    abstract public function invalidValidateValuesProvider() : iterable;
}
