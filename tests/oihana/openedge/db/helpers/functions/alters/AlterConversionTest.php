<?php

namespace tests\oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConversionFunction;
use oihana\openedge\db\enums\Type;
use oihana\reflect\exceptions\ConstantException;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\alterConversion;
use function oihana\openedge\db\helpers\literal;

final class AlterConversionTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testCastInteger(): void
    {
        $sql = alterConversion('products.identifier', ConversionFunction::CAST, [Type::INTEGER]);
        $this->assertEquals('CAST(products.identifier AS INTEGER)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testCastFloatWithPrecision(): void
    {
        $sql = alterConversion('products.price', ConversionFunction::CAST, [Type::FLOAT, 8]);
        $this->assertEquals('CAST(products.price AS FLOAT(8))', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToCharWithFormat(): void
    {
        $sql = alterConversion('order_date', ConversionFunction::TO_CHAR , [ literal( 'YYYY-MM-DD' ) ]);
        $this->assertEquals("TO_CHAR(order_date,'YYYY-MM-DD')", $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToCharWithoutFormat(): void
    {
        $sql = alterConversion('salary', ConversionFunction::TO_CHAR);
        $this->assertEquals('TO_CHAR(salary)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToDate(): void
    {
        $sql = alterConversion('birth_date', ConversionFunction::TO_DATE);
        $this->assertEquals('TO_DATE(birth_date)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToNumber(): void
    {
        $sql = alterConversion('string_number', ConversionFunction::TO_NUMBER);
        $this->assertEquals('TO_NUMBER(string_number)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToTime(): void
    {
        $sql = alterConversion('start_time', ConversionFunction::TO_TIME);
        $this->assertEquals('TO_TIME(start_time)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testToTimestamp(): void
    {
        $sql = alterConversion('created_at', ConversionFunction::TO_TIMESTAMP);
        $this->assertEquals('TO_TIMESTAMP(created_at)', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testDefaultReturnsKey(): void
    {
        $sql = alterConversion('any_column', 'UNKNOWN_FUNCTION');
        $this->assertEquals('any_column', $sql);
    }

    /**
     * @throws ConstantException
     */
    public function testNoFunctionReturnsKey(): void
    {
        $sql = alterConversion('column_only');
        $this->assertEquals('column_only', $sql);
    }
}