<?php

namespace tests\oihana\openedge\db\helpers\functions\conversions;

use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\conversions\toNumber;

final class ToNumberTest extends TestCase
{
    public function testToNumberWithLiteral(): void
    {
        $result = toNumber("'12345'");
        $this->assertStringContainsString('TO_NUMBER', $result);
        $this->assertStringContainsString("'12345'", $result);
    }

    public function testToNumberWithColumn(): void
    {
        $result = toNumber('amount');
        $this->assertSame('TO_NUMBER(amount)', $result);
    }

    public function testToNumberAlwaysReturnsString(): void
    {
        $result = toNumber('price');
        $this->assertIsString($result);
    }
}