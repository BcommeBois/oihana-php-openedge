<?php

namespace tests\oihana\openedge\db\helpers\functions\conversions;

use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\conversions\toTimestamp;

final class ToTimestampTest extends TestCase
{
    public function testToTimestampWithLiteral(): void
    {
        $result = toTimestamp("'2025-08-26 12:34:56'");
        $this->assertStringContainsString('TO_TIMESTAMP', $result);
        $this->assertStringContainsString("'2025-08-26 12:34:56'", $result);
    }

    public function testToTimestampWithColumn(): void
    {
        $result = toTimestamp('created_at');
        $this->assertSame('TO_TIMESTAMP(created_at)', $result);
    }

    public function testToTimestampAlwaysReturnsString(): void
    {
        $result = toTimestamp('updated_at');
        $this->assertIsString($result);
    }
}