<?php

namespace tests\oihana\openedge\db\helpers\functions\conditionals;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\conditionals\coalesce;

class CoalesceTest extends TestCase
{
    #[DataProvider('provideCoalesceCases')]
    public function testCoalesce(array|null $expressions, ?callable $map, string $expected): void
    {
        $result = coalesce($expressions, $map);
        $this->assertSame($expected, $result);
    }

    public static function provideCoalesceCases(): array
    {
        return
        [
            'empty array returns empty string' =>
            [
                [], null, ''
            ],
            'null expressions returns empty string' =>
            [
                null, null, ''
            ],
            'single expression' =>
            [
                ['price'], null, 'COALESCE(price)'
            ],
            'multiple expressions' =>
            [
                ['price', 0], null, 'COALESCE(price,0)'
            ],
            'multiple expressions with string literals' =>
            [
                ['name', "'Unknown'", "'N/A'"], null, "COALESCE(name,'Unknown','N/A')"
            ],
            'with callback map' =>
            [
                ['name', 'city'], fn($v) => "'$v'", "COALESCE('name','city')"
            ],
            'numeric values with callback' =>
            [
                [1, 2, 3], fn($v) => $v * 10, "COALESCE(10,20,30)"
            ],
        ];
    }

}