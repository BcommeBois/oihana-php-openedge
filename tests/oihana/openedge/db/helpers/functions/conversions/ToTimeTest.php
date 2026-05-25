<?php

namespace tests\oihana\openedge\db\helpers\functions\conversions;

use PHPUnit\Framework\TestCase;
use function oihana\openedge\db\helpers\functions\conversions\toTime;

final class ToTimeTest extends TestCase
{
    public function testToTimeWithLiteral(): void
    {
        $result = toTime("'12:34:56'");
        $this->assertStringContainsString('TO_TIME', $result);
        $this->assertStringContainsString("'12:34:56'", $result);
    }

    public function testToTimeWithColumn(): void
    {
        $result = toTime('start_time');
        $this->assertSame('TO_TIME(start_time)', $result);
    }

    public function testToTimeAlwaysReturnsString(): void
    {
        $result = toTime('end_time');
        $this->assertIsString($result);
    }
}