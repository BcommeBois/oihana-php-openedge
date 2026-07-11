<?php

namespace tests\oihana\openedge\db;

use oihana\openedge\db\OpenEdgeQueryBuilder;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class OpenEdgeQueryBuilderTest extends TestCase
{
    public function testSelectDefault(): void
    {
        $this->assertSame( 'SELECT', ( new OpenEdgeQueryBuilder() )->select() );
    }

    public function testSelectDistinct(): void
    {
        $this->assertSame(
            'SELECT DISTINCT',
            ( new OpenEdgeQueryBuilder() )->select( [ OpenEdge::DISTINCT => true ] )
        );
    }

    public function testSelectTop(): void
    {
        $this->assertSame(
            'SELECT TOP 5',
            ( new OpenEdgeQueryBuilder() )->select( [ OpenEdge::TOP => 5 ] )
        );
    }

    public function testSelectStringPassThrough(): void
    {
        $this->assertSame( 'SELECT x', ( new OpenEdgeQueryBuilder() )->select( 'SELECT x' ) );
    }

    public function testCountDefault(): void
    {
        $this->assertSame( 'COUNT(*)', ( new OpenEdgeQueryBuilder() )->count() );
    }

    public function testCountWithCounter(): void
    {
        $this->assertSame(
            'COUNT(id)',
            ( new OpenEdgeQueryBuilder() )->count( [ OpenEdge::COUNTER => 'id' ] )
        );
    }

    public function testDistinctFromConstructor(): void
    {
        $builder = new OpenEdgeQueryBuilder( [ OpenEdge::DISTINCT => true ] );
        $this->assertSame( 'SELECT DISTINCT', $builder->select() );
    }

    public function testComposedTraitsShareConstructorState(): void
    {
        $builder = new OpenEdgeQueryBuilder( [
            OpenEdge::FROM     => 'PUB.produit AS p',
            OpenEdge::COLUMNS  => [ 'id' => 'cod', 'name' => 'nom' ],
            OpenEdge::SORTABLE => [ 'name' => 'nom' ],
            OpenEdge::ORDER_BY => '-name',
        ] );

        $this->assertSame( 'FROM PUB.produit AS p', $builder->from() );
        $this->assertSame( 'cod AS "id", nom AS "name"', $builder->columnList() );
        $this->assertSame( 'ORDER BY nom DESC', $builder->orderBy() );
    }
}
