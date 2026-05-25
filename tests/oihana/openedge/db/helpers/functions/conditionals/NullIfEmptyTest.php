<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions\conditionals;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\conditionals\nullIfEmpty;

final class NullIfEmptyTest extends TestCase
{
    public function testWithColumnName(): void
    {
        $expr = 'username';
        $sql = nullIfEmpty($expr);

        $this->assertStringContainsString('NULLIF', $sql);
        $this->assertStringContainsString($expr, $sql);
        $this->assertStringContainsString("''", $sql);
    }

    public function testWithLiteralEmptyString(): void
    {
        $expr = "''";
        $sql = nullIfEmpty($expr);

        $this->assertStringContainsString('NULLIF', $sql);
        $this->assertStringContainsString($expr, $sql);
    }

    public function testNestedNullIfEmpty(): void
    {
        $expr = nullIfEmpty('col1');
        $nested = nullIfEmpty($expr);

        $this->assertStringContainsString('NULLIF', $nested);
        $this->assertMatchesRegularExpression('/NULLIF\s*\(\s*NULLIF\s*\(/', $nested);
    }

    public function testNumericValue(): void
    {
        $expr = 0;
        $sql = nullIfEmpty($expr);

        $this->assertStringContainsString('NULLIF', $sql);
        $this->assertStringContainsString('0', $sql);
    }

    public function testNonEmptyString(): void
    {
        $expr = "'N/A'";
        $sql = nullIfEmpty($expr);

        $this->assertStringContainsString('NULLIF', $sql);
        $this->assertStringContainsString("'N/A'", $sql);
    }
}