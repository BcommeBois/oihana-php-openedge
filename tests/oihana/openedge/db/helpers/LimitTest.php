<?php

namespace tests\oihana\openedge\db\helpers;

use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\limit;

final class LimitTest extends TestCase
{
    public function testLimitOnly(): void
    {
        $this->assertSame(
            'FETCH FIRST 10 ROWS ONLY',
            limit([OpenEdge::LIMIT => 10])
        );
    }

    public function testOffsetOnly(): void
    {
        $this->assertSame(
            'OFFSET 5 ROWS',
            limit([OpenEdge::OFFSET => 5])
        );
    }

    public function testLimitAndOffset(): void
    {
        $this->assertSame(
            'OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY',
            limit([OpenEdge::OFFSET => 5, OpenEdge::LIMIT => 10])
        );
    }

    public function testEmptyArray(): void
    {
        $this->assertSame(
            '',
            limit([])
        );
    }

    public function testZeroValues(): void
    {
        $this->assertSame(
            '',
            limit([OpenEdge::OFFSET => 0, OpenEdge::LIMIT => 0])
        );
    }

    public function testOffsetZeroLimitPositive(): void
    {
        $this->assertSame(
            'FETCH FIRST 20 ROWS ONLY',
            limit([OpenEdge::OFFSET => 0, OpenEdge::LIMIT => 20])
        );
    }

    public function testOffsetPositiveLimitZero(): void
    {
        $this->assertSame(
            'OFFSET 15 ROWS',
            limit([OpenEdge::OFFSET => 15, OpenEdge::LIMIT => 0])
        );
    }
}