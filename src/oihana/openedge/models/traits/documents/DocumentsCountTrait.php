<?php

namespace oihana\openedge\models\traits\documents;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\exceptions\BindException;
use oihana\exceptions\ValidationException;
use oihana\enums\http\HttpMethod;
use oihana\models\pdo\PDOTrait;
use oihana\openedge\db\traits\OpenEdgeQueryBuilderTrait;
use oihana\openedge\enums\OpenEdge;
use function oihana\core\strings\compile;

trait DocumentsCountTrait
{
    use OpenEdgeQueryBuilderTrait ,
        PDOTrait ;

    /**
     * Returns the number of things.
     *
     * @param array $init The definition of all parameters of the method.
     *
     * @return mixed
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws BindException
     * @throws ValidationException
     */
    public function count( array $init = [] ) :int
    {
        $bindVars = $this->prepareBindVars( $init ) ;

        $key   = $init[ OpenEdge::KEY   ] ?? OpenEdge::ID ;
        $value = $init[ OpenEdge::VALUE ] ?? null ;

        if( isset( $value ) )
        {
            $bindVars[ $key ] = $value ;
        }

        $context = $init[ OpenEdge::CONTEXT ] ?? HttpMethod::count ;

        $sql   = $this->openEdge ;
        $query = compile
        ([
            $sql->select( $init ) ,
            $sql->count( $init ) ,
            $sql->from( $init , false ) , // false -> disabled the joins tables definitions
            $sql->where( $init , $bindVars , $context ) ,
            $sql->withLockingHint( $init ) ,
        ] ) ;

        // $this->debug( __METHOD__ ) ;
        // $this->notice( $query ) ;
        // $this->notice( json_encode( $bindVars ) ) ;

        if( $this->isMock( $init ) )
        {
            $this->logger?->info( __METHOD__ . ' query: ' . $query ) ;
            return 0 ; // do nothing
        }

        return $this->fetchColumn( $query , $bindVars ) ;
    }
}