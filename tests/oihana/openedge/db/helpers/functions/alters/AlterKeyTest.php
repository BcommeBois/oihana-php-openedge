<?php

namespace tests\oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\ConditionalFunction;
use oihana\openedge\db\enums\functions\ConversionFunction;
use oihana\openedge\db\enums\functions\DateFunction;
use oihana\openedge\db\enums\functions\NumericFunction;
use oihana\openedge\db\enums\functions\StringFunction;

use oihana\reflect\exceptions\ConstantException;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\alterKey;

final class AlterKeyTest extends TestCase
{
    /**
     * @throws ConstantException
     */
    public function testApplyIfNullConditionalFunction(): void
    {
        $result = alterKey('price', ConditionalFunction::IFNULL, ['0']);
        $this->assertSame('IFNULL(price,0)', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testApplyNullIfConditionalFunction(): void
    {
        $result = alterKey('status', ConditionalFunction::NULLIF, ["'archived'"]);
        $this->assertSame("NULLIF(status,'archived')", $result);
    }

    /**
     * @throws ConstantException
     */
    public function testApplyCastConversionFunction(): void
    {
        $result = alterKey('amount', ConversionFunction::CAST, ['INTEGER']);
        $this->assertSame('CAST(amount AS INTEGER)', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testApplyDateFunction(): void
    {
        $result = alterKey('created_at', DateFunction::NOW);
        $this->assertSame('NOW()', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testApplyNumericFunction(): void
    {
        $result = alterKey('quantity', NumericFunction::ABS);
        $this->assertSame('ABS(quantity)', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testApplyStringFunction(): void
    {
        $result = alterKey('name', StringFunction::UPPER);
        $this->assertSame('UPPER(name)', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testReturnKeyWhenFunctionIsNull(): void
    {
        $result = alterKey('description', null);
        $this->assertSame('description', $result);
    }

    /**
     * @throws ConstantException
     */
    public function testReturnKeyWhenFunctionIsUnknown(): void
    {
        $result = alterKey('price', 'NON_EXISTING_FUNCTION', ['foo']);
        $this->assertSame('price', $result);
    }
}