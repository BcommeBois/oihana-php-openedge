<?php

namespace oihana\openedge\db\helpers;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\db\enums\Logic;
use oihana\openedge\enums\OpenEdge;

use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\betweenParentheses;
use function oihana\openedge\db\helpers\predicates\preparePredicate;

/**
 * Generates the search condition expression in the WHERE or ON clause.
 * @param mixed $definitions
 * @param array $options
 * @param callable|null $map
 * @return string
 * @throws DependencyException
 * @throws NotFoundException
 */
function searchCondition
(
    mixed     $definitions = null ,
    array     $options     = []   ,
    ?callable $map         = null
)
: string
{
    if ( is_string( $definitions ) )
    {
        return $definitions === Char::EMPTY ? Char::EMPTY : $definitions;
    }

    if ( !is_array( $definitions ) || empty($definitions ) )
    {
        return Char::EMPTY;
    }

    $context        = $options[ OpenEdge::CONTEXT         ] ?? null ;
    $logicOperator  = $options[ OpenEdge::OPERATOR        ] ?? Logic::AND ;
    $useParentheses = $options[ OpenEdge::USE_PARENTHESES ] ?? false ;

    $operator   = $logicOperator ;
    $conditions = $definitions ;

    if ( isAssociative( $definitions ) )
    {
        $conditions = $definitions[ OpenEdge::CONDITIONS ] ?? [] ;
        $operator   = $definitions[ OpenEdge::OPERATOR   ] ?? $logicOperator ;
    }

    $compiled = [] ;

    // TODO use the validateContext in the searchCondition to filter the predicates
    // before the next loop ?

    foreach ($conditions as $predicate)
    {
        $predicate =
            is_array($predicate) && isAssociative($predicate)
            ? searchCondition( $predicate, [ OpenEdge::USE_PARENTHESES => true ] , $map ) // ex: [ conditions => [] , operator : 'OR' ]
            : preparePredicate( $predicate , $context , $map ) ;

        if ( $predicate != Char::EMPTY )
        {
            $compiled[] = $predicate ;
        }
    }

    return betweenParentheses
    (
        expression     : $compiled ,
        useParentheses : $useParentheses ,
        separator      : Char::SPACE . $operator . Char::SPACE ,
        trim           : false
    );
}
