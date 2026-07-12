<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\enums\Join;
use oihana\openedge\db\traits\FromTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class FromTraitTest extends TestCase
{
    private object $query;

    protected function setUp(): void
    {
        $this->query = new class {
            use FromTrait;
        };
        $this->query->from  = null;
        $this->query->joins = null;
    }

    public function testEmptyFromReturnsEmpty(): void
    {
        $this->assertSame( '', $this->query->from() );
    }

    public function testStringFrom(): void
    {
        $this->assertSame(
            'FROM PUB.produit AS products',
            $this->query->from( [ OpenEdge::FROM => 'PUB.produit AS products' ] )
        );
    }

    public function testAssociativeTableWithAlias(): void
    {
        $this->assertSame(
            'FROM DB.produit AS products',
            $this->query->from( [
                OpenEdge::FROM => [ OpenEdge::TABLE => 'DB.produit', OpenEdge::ALIAS => 'products' ],
            ] )
        );
    }

    public function testCrossJoinedTableList(): void
    {
        $this->assertSame(
            'FROM DB.produit AS products, DB.services AS services',
            $this->query->from( [
                OpenEdge::FROM => [
                    [ OpenEdge::TABLE => 'DB.produit',  OpenEdge::ALIAS => 'products' ],
                    [ OpenEdge::TABLE => 'DB.services', OpenEdge::ALIAS => 'services' ],
                ],
            ] )
        );
    }

    public function testStringJoinAppended(): void
    {
        $this->assertSame(
            'FROM t INNER JOIN u ON t.id = u.id',
            $this->query->from( [
                OpenEdge::FROM  => 't',
                OpenEdge::JOINS => 'INNER JOIN u ON t.id = u.id',
            ] )
        );
    }

    public function testStructuredJoinWithOnCondition(): void
    {
        $this->assertSame(
            'FROM DB.products AS products INNER JOIN DB.offers AS offers ON products.id = offers.id',
            $this->query->from( [
                OpenEdge::FROM  => 'DB.products AS products',
                OpenEdge::JOINS => [
                    [
                        OpenEdge::JOIN  => Join::INNER,
                        OpenEdge::TABLE => 'DB.offers',
                        OpenEdge::ALIAS => 'offers',
                        OpenEdge::ON    => [
                            [ [ OpenEdge::COLUMN => 'products.id' ], '=', [ OpenEdge::COLUMN => 'offers.id' ] ],
                        ],
                    ],
                ],
            ] )
        );
    }

    public function testJoinsArrayWithAStringEntry(): void
    {
        $this->assertSame(
            'FROM t INNER JOIN u ON t.id = u.id',
            $this->query->from( [
                OpenEdge::FROM  => 't',
                OpenEdge::JOINS => [ 'INNER JOIN u ON t.id = u.id' ],
            ] )
        );
    }

    public function testNoReorderAppended(): void
    {
        $this->assertSame(
            'FROM t { NO REORDER }',
            $this->query->from( [ OpenEdge::FROM => 't', OpenEdge::NO_REORDER => true ] )
        );
    }

    public function testFromPropertyFallback(): void
    {
        $this->query->from = 'PUB.produit AS products';
        $this->assertSame( 'FROM PUB.produit AS products', $this->query->from() );
    }

    public function testTableRefWithNonAssociativeReturnsEmpty(): void
    {
        $this->assertSame( '', $this->query->tableRef( null ) );
        $this->assertSame( '', $this->query->tableRef( 'plain string' ) );
    }

    public function testJoinedTablesWithoutJoinsReturnsEmpty(): void
    {
        $this->assertSame( '', $this->query->joinedTables() );
    }
}
