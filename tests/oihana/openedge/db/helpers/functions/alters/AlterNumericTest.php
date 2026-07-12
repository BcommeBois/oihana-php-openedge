<?php

namespace tests\oihana\openedge\db\helpers\functions\alters;

use oihana\openedge\db\enums\functions\NumericFunction;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\alters\alterNumeric;

final class AlterNumericTest extends TestCase
{
    public function testSign(): void
    {
        $this->assertSame( 'SIGN(k)', alterNumeric( 'k', NumericFunction::SIGN ) );
    }

    public function testSin(): void
    {
        $this->assertSame( 'SIN(k)', alterNumeric( 'k', NumericFunction::SIN ) );
    }

    public function testSqrt(): void
    {
        $this->assertSame( 'SQRT(k)', alterNumeric( 'k', NumericFunction::SQRT ) );
    }

    public function testTan(): void
    {
        $this->assertSame( 'TAN(k)', alterNumeric( 'k', NumericFunction::TAN ) );
    }

    public function testUnknownFunctionReturnsKey(): void
    {
        $this->assertSame( 'k', alterNumeric( 'k', 'UNKNOWN' ) );
    }
}
