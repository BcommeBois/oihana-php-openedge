<?php

namespace oihana\openedge\models\traits;

use oihana\enums\Char;
use oihana\models\pdo\PDOTrait;
use PDOStatement;

/**
 * Provides helper methods specific to OpenEdge SQL Server, including:
 * - Setting query execution timeouts at connection or server level.
 * - Updating table and index statistics.
 *
 * This trait requires PDOTrait for database access.
 *
 * Usage example:
 * ```php
 * $this->connectTimeout(30);  // Set a 30-second query timeout for the current connection
 * $this->serverTimeout(60);   // Set a 60-second query timeout at server level
 * $this->updateStatistics('customer'); // Refresh statistics for the 'customer' table
 * ```
 *
 * @package oihana\openedge\models\traits
 */
trait OpenEdgeHelperTrait
{
    use PDOTrait ;

    /**
     * Defines the maximum number of seconds during which a query should execute for the current SQL Server connection.
     * @param int $delay Time in seconds
     * @return void
     */
    public function connectTimeout( int $delay = 0 ):void
    {
        $query     = "SET PRO_CONNECT QUERY_TIMEOUT :delay" ;
        $statement = $this->pdo?->prepare( $query ) ;
        if( $statement instanceof PDOStatement )
        {
            $statement->bindValue( Char::COLON . 'delay' , $delay );
            if( $statement->execute() )
            {
                $statement->closeCursor() ;
                $statement = null ;
            }
        }
    }

    /**
     * Defines the maximum number of seconds during which a query should execute for the current
     * SQL Server connection.
     * @param int $delay The number of seconds.
     * @return void
     */
    public function serverTimeout( int $delay = 0 ):void
    {
        $query     = "SET PRO_SERVER QUERY_TIMEOUT :delay" ;
        $statement = $this->pdo->prepare( $query ) ;
        if( $statement instanceof PDOStatement )
        {
            $statement->bindValue( Char::COLON . 'delay' , $delay );
            $statement->execute() ;
            $statement->closeCursor() ;
            $statement = null ;
        }
    }

    public function updateStatistics( string $table ) :void
    {
        $query = "UPDATE TABLE STATISTICS AND INDEX STATISTICS AND ALL COLUMN STATISTICS FOR " . $table ;
        $statement = $this->pdo->prepare( $query ) ;
        if( $statement instanceof PDOStatement )
        {
            $statement->execute() ;
            $statement->closeCursor() ;
        }
        $statement = null ;
    }
}