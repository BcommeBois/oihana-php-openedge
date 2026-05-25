<?php

declare(strict_types=1);

namespace tests\oihana\openedge\db\helpers\functions\conditionals;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\functions\conditionals\nullIfZero;

final class NullIfZeroTest extends TestCase
{
    /**
     * Verifies that the function generates the correct NULLIF SQL syntax.
     */
    #[Test]
    #[DataProvider('provideExpressions')]
    public function it_generates_nullif_statement( mixed $expression , string $expected ) :void
    {
        self::assertSame( $expected , nullIfZero( $expression ) );
    }

    /**
     * Data provider for input scenarios.
     *
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function provideExpressions() :array
    {
        return [
            'Simple column' => [
                'latitude' ,
                'NULLIF(latitude, 0)'
            ],
            'Dotted column (Table alias)' => [
                'geo.elevation' ,
                'NULLIF(geo.elevation, 0)'
            ],
            'Integer value' => [
                100 ,
                'NULLIF(100, 0)'
            ],
            'Float value' => [
                45.5 ,
                'NULLIF(45.5, 0)'
            ],
            'String literal (SQL)' => [
                "'N/A'" ,
                "NULLIF('N/A', 0)"
            ],
            'Zero integer (Edge case)' => [
                0 ,
                'NULLIF(0, 0)'
            ],
            'Numeric String' => [
                "123" ,
                'NULLIF(123, 0)'
            ],
        ];
    }
}