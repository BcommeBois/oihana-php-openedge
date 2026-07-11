<?php

namespace tests\oihana\openedge\db\helpers;

use PHPUnit\Framework\TestCase;

use function oihana\openedge\db\helpers\orderByExpression;

final class OrderByExpressionTest extends TestCase
{
    private const array SORTABLE = [
        'name'    => 'user_name',
        'city'    => 'city_name',
        'created' => 'created_at',
    ];

    public function testNullExpressionReturnsEmptyArray(): void
    {
        $this->assertSame( [], orderByExpression( null, self::SORTABLE ) );
    }

    public function testEmptyExpressionReturnsEmptyArray(): void
    {
        $this->assertSame( [], orderByExpression( '', self::SORTABLE ) );
    }

    public function testNullSortableReturnsEmptyArray(): void
    {
        $this->assertSame( [], orderByExpression( 'name', null ) );
    }

    public function testEmptySortableReturnsEmptyArray(): void
    {
        $this->assertSame( [], orderByExpression( 'name', [] ) );
    }

    public function testSingleAscendingKey(): void
    {
        $this->assertSame( [ 'user_name' ], orderByExpression( 'name', self::SORTABLE ) );
    }

    public function testSingleDescendingKey(): void
    {
        $this->assertSame( [ 'user_name DESC' ], orderByExpression( '-name', self::SORTABLE ) );
    }

    public function testMixedAscendingAndDescending(): void
    {
        $this->assertSame(
            [ 'user_name DESC', 'city_name', 'created_at DESC' ],
            orderByExpression( '-name,city,-created', self::SORTABLE )
        );
    }

    public function testUnknownKeysAreIgnored(): void
    {
        $this->assertSame(
            [ 'user_name' ],
            orderByExpression( 'name,unknown,-missing', self::SORTABLE )
        );
    }

    public function testEmptySegmentsAreSkipped(): void
    {
        $this->assertSame(
            [ 'user_name', 'city_name' ],
            orderByExpression( 'name,,city', self::SORTABLE )
        );
    }

    public function testWhitespaceIsNotTrimmedFromKeys(): void
    {
        // The function does not trim keys, so " name" and "city " never match
        // the sortable map and are silently dropped.
        $this->assertSame( [], orderByExpression( ' name, city ', self::SORTABLE ) );
    }

    public function testAllUnknownReturnsEmptyArray(): void
    {
        $this->assertSame( [], orderByExpression( 'foo,-bar', self::SORTABLE ) );
    }
}
