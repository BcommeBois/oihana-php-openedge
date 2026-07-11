<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\traits\OrderByTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class OrderByTraitTest extends TestCase
{
    private object $query;

    protected function setUp(): void
    {
        $this->query = new class {
            use OrderByTrait;
        };
        $this->query->orderBy  = null;
        $this->query->sortable = [
            'name' => 'user_name',
            'city' => 'city_name',
        ];
    }

    public function testEmptyWhenNothingProvided(): void
    {
        $this->assertSame( '', $this->query->orderBy() );
    }

    public function testSortKey(): void
    {
        $this->assertSame(
            'ORDER BY user_name DESC, city_name',
            $this->query->orderBy( [ OpenEdge::SORT => '-name,city' ] )
        );
    }

    public function testOrderByKeyIsFallbackForSort(): void
    {
        $this->assertSame(
            'ORDER BY user_name',
            $this->query->orderBy( [ OpenEdge::ORDER_BY => 'name' ] )
        );
    }

    public function testSortTakesPriorityOverOrderBy(): void
    {
        $this->assertSame(
            'ORDER BY city_name',
            $this->query->orderBy( [ OpenEdge::SORT => 'city', OpenEdge::ORDER_BY => 'name' ] )
        );
    }

    public function testDefaultOrderByProperty(): void
    {
        $this->query->orderBy = '-name';
        $this->assertSame( 'ORDER BY user_name DESC', $this->query->orderBy() );
    }

    public function testUnknownKeysProduceEmptyClause(): void
    {
        $this->assertSame( '', $this->query->orderBy( [ OpenEdge::SORT => 'unknown,-missing' ] ) );
    }

    public function testNullSortableProducesEmptyClause(): void
    {
        $this->query->sortable = null;
        $this->assertSame( '', $this->query->orderBy( [ OpenEdge::SORT => 'name' ] ) );
    }
}
