<?php

namespace tests\oihana\openedge\db\traits;

use DI\Container;
use oihana\models\enums\ModelParam;
use oihana\openedge\db\OpenEdgeQueryBuilder;
use oihana\openedge\db\traits\OpenEdgeQueryBuilderTrait;
use oihana\openedge\enums\OpenEdge;
use PHPUnit\Framework\TestCase;

final class OpenEdgeQueryBuilderTraitTest extends TestCase
{
    private function host(): object
    {
        return new class {
            use OpenEdgeQueryBuilderTrait;
        };
    }

    public function testCreatesBuilderByDefaultAndReturnsStatic(): void
    {
        $host = $this->host();
        $result = $host->initializeOpenEdgeQueryBuilder();

        $this->assertSame( $host, $result );
        $this->assertInstanceOf( OpenEdgeQueryBuilder::class, $host->openEdge );
    }

    public function testReusesExistingBuilderInstance(): void
    {
        $existing = new OpenEdgeQueryBuilder( [ OpenEdge::FROM => 't' ] );
        $host     = $this->host();

        $host->initializeOpenEdgeQueryBuilder( [ ModelParam::QUERY_BUILDER => $existing ] );

        $this->assertSame( $existing, $host->openEdge );
    }

    public function testArrayBuilderConfigInitializesNewBuilder(): void
    {
        $host = $this->host();
        $host->initializeOpenEdgeQueryBuilder( [
            ModelParam::QUERY_BUILDER => [ OpenEdge::FROM => 'PUB.x AS x' ],
        ] );

        $this->assertInstanceOf( OpenEdgeQueryBuilder::class, $host->openEdge );
        $this->assertSame( 'FROM PUB.x AS x', $host->openEdge->from() );
    }

    public function testResolvesBuilderFromContainerByName(): void
    {
        $existing  = new OpenEdgeQueryBuilder( [ OpenEdge::FROM => 't' ] );
        $container = new Container();
        $container->set( 'myBuilder', $existing );

        $host = $this->host();
        $host->initializeOpenEdgeQueryBuilder(
            [ ModelParam::QUERY_BUILDER => 'myBuilder' ],
            $container
        );

        $this->assertSame( $existing, $host->openEdge );
    }

    public function testFallsBackToInitWhenContainerMissesName(): void
    {
        $host = $this->host();
        $host->initializeOpenEdgeQueryBuilder(
            [ ModelParam::QUERY_BUILDER => 'unknown', OpenEdge::FROM => 'PUB.y AS y' ],
            new Container()
        );

        $this->assertInstanceOf( OpenEdgeQueryBuilder::class, $host->openEdge );
        $this->assertSame( 'FROM PUB.y AS y', $host->openEdge->from() );
    }
}
