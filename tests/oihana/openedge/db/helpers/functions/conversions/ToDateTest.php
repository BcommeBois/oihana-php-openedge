<?php

namespace tests\oihana\openedge\db\helpers\functions\conversions;

use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\conversions\toDate;

final class ToDateTest extends TestCase
{
    public function testToDateWithLiteral(): void
    {
        $result = toDate("'2025-08-26'");
        $this->assertStringContainsString('TO_DATE', $result);
        $this->assertStringContainsString("'2025-08-26'", $result);
    }

    public function testToDateWithColumn(): void
    {
        $result = toDate('order_date');
        $this->assertSame('TO_DATE(order_date)', $result);
    }

    public function testToDateAlwaysReturnsString(): void
    {
        $result = toDate('some_column');
        $this->assertIsString($result);
    }
}