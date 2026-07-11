<?php

namespace tests\oihana\openedge\integration;

use oihana\openedge\db\OpenEdgePDOBuilder;
use PDO;
use PDOException;

use function oihana\init\initConfig;

/**
 * Guard for the live OpenEdge integration tests.
 *
 * Connection settings are read from `configs/config.toml` (copied from
 * `configs/config.example.toml`, gitignored) — the same `[odbc]` /
 * `[databases.<name>]` split documented in wiki/{fr,en}/connection.md. No
 * credentials are committed, and no environment variables are involved.
 *
 * A test using {@see connectOpenEdge()} skips gracefully (rather than failing)
 * whenever a real server can't be reached:
 *
 * - the `pdo_odbc` extension is not loaded,
 * - `configs/config.toml` is missing or has no `[odbc]` / `[databases.*]`,
 * - or the connection attempt throws (driver missing, server unreachable, …).
 */
trait RequiresOpenEdgeTrait
{
    /**
     * Builds a live PDO connection through {@see OpenEdgePDOBuilder}, or skips
     * the current test when a real OpenEdge server can't be reached.
     */
    private function connectOpenEdge(): PDO
    {
        if ( ! extension_loaded( 'pdo_odbc' ) )
        {
            $this->markTestSkipped( 'The pdo_odbc extension (Progress ODBC driver) is not installed.' );
        }

        $config    = $this->openEdgeConfig() ;
        $odbc      = is_array( $config[ 'odbc' ]      ?? null ) ? $config[ 'odbc' ]      : [] ;
        $databases = is_array( $config[ 'databases' ] ?? null ) ? $config[ 'databases' ] : [] ;

        if ( $odbc === [] || $databases === [] )
        {
            $this->markTestSkipped(
                'No OpenEdge connection configured — copy configs/config.example.toml to '
                . 'configs/config.toml and fill the [odbc] and [databases.*] sections.'
            );
        }

        // Connect to the first declared database block.
        $database = reset( $databases ) ;

        $builder = new OpenEdgePDOBuilder( [
            ...$odbc,
            ...( is_array( $database ) ? $database : [] ),
        ] ) ;

        try
        {
            return $builder() ;
        }
        catch ( PDOException $e )
        {
            $this->markTestSkipped( 'OpenEdge server unreachable: ' . $e->getMessage() ) ;
        }
    }

    /**
     * Loads `configs/config.toml`. Returns an empty array when the file is
     * absent (initConfig swallows the missing-file case).
     *
     * @return array<string,mixed>
     */
    protected function openEdgeConfig(): array
    {
        $configsDir = dirname( __DIR__, 4 ) . DIRECTORY_SEPARATOR . 'configs' ;
        return initConfig( basePath: $configsDir, file: 'config.toml' ) ;
    }
}
