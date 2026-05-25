<?php

namespace oihana\openedge\db\traits;

use oihana\enums\Char;
use oihana\exceptions\BindException;
use oihana\openedge\db\enums\Facet;
use oihana\openedge\db\enums\Logic;
use oihana\openedge\db\enums\Predicate;
use oihana\openedge\enums\OpenEdge;
use oihana\openedge\enums\OpenEdge as SQL;

use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\predicates;
use function oihana\openedge\db\helpers\predicates\prepareInPredicate;

trait FacetsTrait
{
    /**
     * The locally facets settings reference.
     */
    public ?array $facets = [] ;

    /**
     * Initialize the local facets configuration.
     * @param array $init
     * @return static
     */
    public function initializeFacets( array $init = [] ) :static
    {
        $this->facets = $init[ OpenEdge::FACETS ] ?? $this->facets ;
        return $this ;
    }

    /**
     * Prepares the facets predicates.
     * @param mixed $init
     * @param array|null $bindVars
     * @param string $logicalOperator
     * @return ?string
     * @throws BindException
     */
    public function prepareFacets( mixed $init = [] , ?array &$bindVars = [] , string $logicalOperator = Logic::AND ) : ?string
    {
        if( is_array( $init ) && isAssociative( $init )
            && is_array( $this->facets ) && count( $this->facets ) > 0 )
        {
            $facets = $init[ SQL::FACETS ] ?? null ;
            if( is_array( $facets ) && count( $facets ) > 0 )
            {
                $predicates = [] ;
                foreach( $facets as $key => $value )
                {
                    $facet = $this->facets[ $key ] ?? null ;
                    if( isset( $facet ) )
                    {
                        $type = $facet[ SQL::TYPE ] ?? null ;
                        switch( $type )
                        {
                            case Facet::IN :
                            {
                                $this->prepareFacetIn( $key , $value , $bindVars , $facet , $predicates ) ;
                                break ;
                            }
                        }
                    }
                }
                return predicates( $predicates , $logicalOperator ) ;
            }
        }
        return null ;
    }

    /**
     * Prepare a "contains" facet.
     * @param string $key
     * @param mixed $values
     * @param array $bindVars
     * @param array $facet
     * @param array $predicates
     * @return void
     * @throws BindException
     */
    protected function prepareFacetIn( string $key , mixed $values , array &$bindVars , array $facet , array &$predicates = [] ):void
    {
        $expression = $facet[ Facet::EXPRESSION ] ?? null ;
        if( isset( $expression ) )
        {
            if( is_string( $values ) )
            {
                $values = explode( Char::COMMA , $values ) ;
            }

            if( is_array( $values ) )
            {
                $conditions = [] ;
                foreach( $values as $subKey => $value )
                {
                    $conditions[] = $this->toBindExpression( $value , $bindVars , $key . Char::UNDERLINE . $subKey ) ;
                }

                if( count( $conditions ) > 0 )
                {
                    $predicate = prepareInPredicate( $expression , Predicate::IN , $conditions ) ;
                    if( $predicate != Char::EMPTY )
                    {
                        $predicates[] = $predicate ;
                    }
                }
            }
        }
    }
}