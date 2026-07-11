<?php

namespace tests\oihana\openedge\db\traits;

use oihana\openedge\db\enums\Facet;
use oihana\openedge\db\traits\BindTrait;
use oihana\openedge\db\traits\FacetsTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class FacetsTraitTest extends TestCase
{
    private object $query;

    protected function setUp(): void
    {
        $this->query = new class {
            use FacetsTrait;
            use BindTrait;
        };
        $this->query->initializeQueryID( 'q' );
        $this->query->facets = [
            'status' => [ OpenEdge::TYPE => Facet::IN, Facet::EXPRESSION => 'products.status' ],
        ];
    }

    public function testInitializeFacetsOverridesConfig(): void
    {
        $this->query->initializeFacets( [ OpenEdge::FACETS => [ 'a' => [ 'x' => 1 ] ] ] );
        $this->assertSame( [ 'a' => [ 'x' => 1 ] ], $this->query->facets );
    }

    public function testInPredicateBuiltFromCommaSeparatedValues(): void
    {
        $binds = [];
        $out   = $this->query->prepareFacets( [ OpenEdge::FACETS => [ 'status' => '1,2,3' ] ], $binds );

        $this->assertSame( "'products.status' IN (status_0,status_1,status_2)", $out );
        $this->assertSame( [ 'status_0' => '1', 'status_1' => '2', 'status_2' => '3' ], $binds );
    }

    public function testInPredicateFromArrayValues(): void
    {
        $binds = [];
        $out   = $this->query->prepareFacets( [ OpenEdge::FACETS => [ 'status' => [ 'A', 'B' ] ] ], $binds );

        $this->assertSame( "'products.status' IN (status_0,status_1)", $out );
        $this->assertSame( [ 'status_0' => 'A', 'status_1' => 'B' ], $binds );
    }

    public function testNoFacetsInInitReturnsNull(): void
    {
        $binds = [];
        $this->assertNull( $this->query->prepareFacets( [], $binds ) );
    }

    public function testNonAssociativeInitReturnsNull(): void
    {
        $binds = [];
        $this->assertNull( $this->query->prepareFacets( [ 1, 2, 3 ], $binds ) );
    }

    public function testUnknownFacetKeyIsIgnored(): void
    {
        // No known facet matches → no predicate built → predicates([]) is null.
        $binds = [];
        $out   = $this->query->prepareFacets( [ OpenEdge::FACETS => [ 'unknown' => '1' ] ], $binds );

        $this->assertNull( $out );
        $this->assertSame( [], $binds );
    }

    public function testEmptyFacetConfigReturnsNull(): void
    {
        $this->query->facets = [];
        $binds = [];
        $this->assertNull( $this->query->prepareFacets( [ OpenEdge::FACETS => [ 'status' => '1' ] ], $binds ) );
    }
}
