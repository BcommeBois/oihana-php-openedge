<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;

use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\enums\OpenEdge;

use oihana\reflect\exceptions\ConstantException ;

use function oihana\core\arrays\isAssociative ;

/**
 * Defines an OpenEdge SQL expression. An expression can be a literal (string, number, date, etc.),
 * a column definition or a set of expressions (concat).
 *
 * To initialize a column, the $definition parameter is an associative array
 * with property 'column' and the optional properties : 'table', 'cast', 'alter', 'alters', 'nullable'.
 *
 * To initialize a set of expressions, use the property 'concat'.
 *
 * @param mixed $definition The definition of the expression.
 * @param string $default
 *
 * @return mixed
 *
 * @throws ConstantException
 * @throws DateInvalidTimeZoneException
 * @throws DateMalformedStringException
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @example
 * 1 - Creates a literal expression
 * ```
 * $literal = expression( 'hello world' ) ;
 * echo $literal // 'hello world'
 * ```
 *
 * 2 - Creates a column expression
 * <code>
 * echo expression
 * ([
 *    OpenEdge::COLUMN => 'name' ,
 *    OpenEdge::TABLE  => 'clients' ,
 *    OpenEdge::CAST   => [ Type::VAR_CHAR , 55 ] ,
 *    OpenEdge::ALTER => StringFunction::LOWER
 * ]) ; // LOWER( CAST( clients.name AS VARCHAR(55) ) )
 * </code>
 *
 * 3 - Concat a set of expressions
 * ```
 * $column = expression
 * ([
 *    OpenEdge::CONCAT =>
 *    [
 *       [ OpenEdge::COLUMN => 'firstName' , OpenEdge::TABLE => 'clients' ] ,
 *       ' - ' ,
 *       [ OpenEdge::COLUMN => 'lastName' , OpenEdge::TABLE => 'clients' ] ,
 *    ]
 * ]) ; // clients.firstName || ' - ' || clients.lastName
 * ```
 * 4 - Creates an array expression (string separated by ';')
 * ```
 * echo expression
 * ([
 *    OpenEdge::ARRAY =>
 *    [
 *       [ OpenEdge::COLUMN => 'firstName' , OpenEdge::TABLE => 'clients' ] ,
 *       [ OpenEdge::COLUMN => 'lastName' , OpenEdge::TABLE => 'clients' ] ,
 *    ]
 * ]) ; // clients.firstName || ';' || clients.lastName
 * ```
 *
 * 5 - List a set of expressions
 * By default with the ',' separator :
 * ```
 * echo expression
 * ([
 *    OpenEdge::LIST =>
 *    [
 *       [ OpenEdge::COLUMN => 'firstName' , OpenEdge::TABLE => 'clients' ] ,
 *       [ OpenEdge::COLUMN => 'lastName' , OpenEdge::TABLE => 'clients' ] ,
 *    ]
 * ]) ; // clients.firstName || ',' || clients.lastName
 * ```
 *
 * Use a custom separator to list a set of expressions :
 * ```
 * echo expression
 * ([
 *    OpenEdge::SEPARATOR => ';' ,
 *    OpenEdge::LIST      =>
 *    [
 *       [ OpenEdge::COLUMN => 'firstName' , OpenEdge::TABLE => 'clients' ] ,
 *       [ OpenEdge::COLUMN => 'lastName' , OpenEdge::TABLE => 'clients' ] ,
 *    ]
 * ]) ; // clients.firstName || ';' || clients.lastName
 * ```
 */
function expression( mixed $definition , string $default = Char::EMPTY ) : mixed
{
    if ( is_object( $definition ) )
    {
        $definition = (array) $definition ;
    }

    if ( is_array( $definition ) && isAssociative( $definition ) )
    {
        $callable = fn( ...$args ) :mixed => expression( ...$args ) ;
        return match ( true )
        {
            isset( $definition[ OpenEdge::BIND   ] ) => bindExpression  ( $definition , $callable ) ,
            isset( $definition[ OpenEdge::VALUE  ] ) => valueExpression ( $definition , $callable ) ,
            isset( $definition[ OpenEdge::CASE   ] ) => caseExpression  ( $definition , $callable ) ,
            isset( $definition[ OpenEdge::ARRAY  ] ) ,
            isset( $definition[ OpenEdge::CONCAT ] ) ,
            isset( $definition[ OpenEdge::LIST   ] ) => concatExpression( $definition , $callable ) ,
            default                                  => columnExpression( $definition , $default , $callable ) ,
        };
    }

    return literal( $definition ) ?? Char::EMPTY ;
}

// case isset( $definition[ OpenEdge::QUERY ] ) :
// {
//     return queryExpression( $definition ) ; // TODO implement the query expression
// }