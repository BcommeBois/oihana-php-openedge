<?php

namespace tests\oihana\openedge\integration;

use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Live smoke test against a real Progress OpenEdge SQL server.
 *
 * Tagged `integration`, so it is excluded from `composer test` and only runs
 * via `composer test:integration` (phpunit-integration.xml, failOnSkipped=false).
 * Without a reachable server it skips green — see {@see RequiresOpenEdgeTrait}.
 */
#[Group( 'integration' )]
final class OpenEdgeConnectionTest extends TestCase
{
    use RequiresOpenEdgeTrait;

    public function testConnectionSucceeds(): void
    {
        $pdo = $this->connectOpenEdge();

        $this->assertInstanceOf( PDO::class, $pdo );
    }

    public function testSmokeQuery(): void
    {
        $query = $this->openEdgeConfig()[ 'test' ][ 'query' ] ?? null;

        if ( ! is_string( $query ) || $query === '' )
        {
            $this->markTestSkipped(
                'Set [test] query in configs/config.toml to run a smoke query '
                . '(e.g. "SELECT COUNT(*) FROM PUB.customer").'
            );
        }

        $pdo       = $this->connectOpenEdge();
        $statement = $pdo->query( $query );

        $this->assertNotFalse( $statement, 'The smoke query should execute against OpenEdge.' );
    }
}
