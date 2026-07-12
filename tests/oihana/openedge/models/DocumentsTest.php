<?php

namespace tests\oihana\openedge\models;

use DI\Container;
use oihana\exceptions\UnsupportedOperationException;
use oihana\openedge\models\Documents;
use oihana\openedge\enums\OpenEdge;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class DocumentsTest extends TestCase
{
    /**
     * Builds a Documents model wired on a plain container, an optional PDO and
     * a capturing logger, with a small default query-builder configuration.
     */
    private function documents( ?PDO $pdo = null, array $init = [] ): array
    {
        $logger = new class extends AbstractLogger {
            public array $messages = [];
            public function log( $level, string|\Stringable $message, array $context = [] ): void
            {
                $this->messages[] = (string) $message;
            }
        };

        // initializeLogger only accepts a logger *instance* as its first arg or a
        // service id resolved from the container — an instance passed inside the
        // init array is discarded. Register it and pass its id.
        $container = new Container();
        $container->set( 'test.logger', $logger );

        $docs = new Documents( $container, [
            OpenEdge::LOGGER  => 'test.logger',
            OpenEdge::PDO     => $pdo,
            'from'            => 'PUB.produit AS p',
            'columns'         => [ 'id' => 'cod', 'name' => 'nom' ],
            ...$init,
        ] );

        return [ $docs, $logger ];
    }

    /** A PDOStatement stub whose fetch/fetchAll return the given canned rows. */
    private function statement( mixed $fetch = null, array $fetchAll = [] ): PDOStatement
    {
        $stmt = $this->createStub( PDOStatement::class );
        $stmt->method( 'execute' )->willReturn( true );
        $stmt->method( 'setFetchMode' )->willReturn( true );
        $stmt->method( 'closeCursor' )->willReturn( true );
        $stmt->method( 'fetch' )->willReturn( $fetch );
        $stmt->method( 'fetchAll' )->willReturn( $fetchAll );
        return $stmt;
    }

    /** A PDO stub whose prepare() always returns the given statement. */
    private function pdoReturning( PDOStatement $stmt ): PDO
    {
        $pdo = $this->createStub( PDO::class );
        $pdo->method( 'prepare' )->willReturn( $stmt );
        return $pdo;
    }

    // --- count / exist : exercised through the debug+mock seam (no PDO) ------

    public function testCountMockSeamBuildsSqlAndReturnsZero(): void
    {
        [ $docs, $logger ] = $this->documents();

        $result = $docs->count( [ OpenEdge::MOCK => true, OpenEdge::DEBUG => true, OpenEdge::VALUE => 1 ] );

        $this->assertSame( 0, $result );
        $joined = implode( "\n", $logger->messages );
        $this->assertStringContainsString( 'COUNT(*)', $joined );
        $this->assertStringContainsString( 'FROM PUB.produit AS p', $joined );
    }

    public function testExistMockSeamReturnsFalseWhenCountIsZero(): void
    {
        [ $docs ] = $this->documents();

        $this->assertFalse(
            $docs->exist( [ OpenEdge::MOCK => true, OpenEdge::DEBUG => true, OpenEdge::VALUE => 1 ] )
        );
    }

    // --- get : exercised through a mocked PDO -------------------------------

    public function testGetReturnsFetchedRowAsObject(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetch: [ 'id' => 123, 'name' => 'foo' ] ) );

        [ $docs ] = $this->documents( $pdo );
        $thing = $docs->get( [ OpenEdge::VALUE => 123 ] );

        $this->assertIsObject( $thing );
        $this->assertSame( 123, $thing->id );
        $this->assertSame( 'foo', $thing->name );
    }

    public function testGetReturnsNullWhenNoRow(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetch: false ) );

        [ $docs ] = $this->documents( $pdo );

        $this->assertNull( $docs->get( [ OpenEdge::VALUE => 999 ] ) );
    }

    public function testGetWithDebugLogsTheQuery(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetch: [ 'id' => 1 ] ) );
        [ $docs, $logger ] = $this->documents( $pdo );

        $docs->get( [ OpenEdge::VALUE => 1, OpenEdge::DEBUG => true ] );

        $this->assertNotEmpty( $logger->messages );
    }

    // --- list : exercised through a mocked PDO ------------------------------

    public function testListReturnsFetchedRows(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetchAll: [
            [ 'id' => 1, 'name' => 'a' ],
            [ 'id' => 2, 'name' => 'b' ],
        ] ) );

        [ $docs ] = $this->documents( $pdo );
        $things = $docs->list();

        $this->assertCount( 2, $things );
    }

    public function testListReturnsEmptyArrayWhenNoRows(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetchAll: [] ) );

        [ $docs ] = $this->documents( $pdo );

        $this->assertSame( [], $docs->list() );
    }

    public function testListWithDebugLogsTheQuery(): void
    {
        $pdo = $this->pdoReturning( $this->statement( fetchAll: [ [ 'id' => 1 ] ] ) );
        [ $docs, $logger ] = $this->documents( $pdo );

        $docs->list( [ OpenEdge::DEBUG => true ] );

        $this->assertNotEmpty( $logger->messages );
    }

    // --- Read-only guards : every mutation is unsupported -------------------

    public static function unsupportedOperations(): array
    {
        return [
            'insert'     => [ 'insert' ],
            'update'     => [ 'update' ],
            'updateDate' => [ 'updateDate' ],
            'replace'    => [ 'replace' ],
            'upsert'     => [ 'upsert' ],
            'delete'     => [ 'delete' ],
            'deleteAll'  => [ 'deleteAll' ],
            'last'       => [ 'last' ],
            'truncate'   => [ 'truncate' ],
        ];
    }

    #[DataProvider( 'unsupportedOperations' )]
    public function testMutationIsUnsupported( string $method ): void
    {
        [ $docs ] = $this->documents();

        $this->expectException( UnsupportedOperationException::class );
        $docs->$method();
    }

    // --- stream : generator-backed read through a mocked PDO ----------------

    public function testStreamYieldsFetchedThings(): void
    {
        $stmt = $this->createStub( PDOStatement::class );
        $stmt->method( 'execute' )->willReturn( true );
        $stmt->method( 'setFetchMode' )->willReturn( true );
        $stmt->method( 'closeCursor' )->willReturn( true );
        // fetch() is called in a while-loop; return two rows then false to stop.
        $stmt->method( 'fetch' )->willReturnOnConsecutiveCalls(
            [ 'id' => 1, 'name' => 'a' ],
            [ 'id' => 2, 'name' => 'b' ],
            false
        );

        [ $docs ] = $this->documents( $this->pdoReturning( $stmt ) );

        $things = iterator_to_array( $docs->stream() );

        $this->assertCount( 2, $things );
    }

    // --- OpenEdgeHelperTrait : direct PDO statements ------------------------

    public function testConnectTimeoutPreparesAndExecutes(): void
    {
        $stmt = $this->createMock( PDOStatement::class );
        $stmt->expects( $this->once() )->method( 'execute' )->willReturn( true );
        $stmt->expects( $this->once() )->method( 'closeCursor' )->willReturn( true );

        $pdo = $this->createMock( PDO::class );
        $pdo->expects( $this->once() )
            ->method( 'prepare' )
            ->with( 'SET PRO_CONNECT QUERY_TIMEOUT :delay' )
            ->willReturn( $stmt );

        [ $docs ] = $this->documents( $pdo );
        $docs->connectTimeout( 30 );
    }

    public function testServerTimeoutPreparesAndExecutes(): void
    {
        $stmt = $this->createMock( PDOStatement::class );
        $stmt->expects( $this->once() )->method( 'execute' )->willReturn( true );

        $pdo = $this->createMock( PDO::class );
        $pdo->expects( $this->once() )
            ->method( 'prepare' )
            ->with( 'SET PRO_SERVER QUERY_TIMEOUT :delay' )
            ->willReturn( $stmt );

        [ $docs ] = $this->documents( $pdo );
        $docs->serverTimeout( 60 );
    }

    public function testUpdateStatisticsPreparesForTable(): void
    {
        $stmt = $this->createMock( PDOStatement::class );
        $stmt->expects( $this->once() )->method( 'execute' )->willReturn( true );

        $pdo = $this->createMock( PDO::class );
        $pdo->expects( $this->once() )
            ->method( 'prepare' )
            ->with( 'UPDATE TABLE STATISTICS AND INDEX STATISTICS AND ALL COLUMN STATISTICS FOR customer' )
            ->willReturn( $stmt );

        [ $docs ] = $this->documents( $pdo );
        $docs->updateStatistics( 'customer' );
    }
}
