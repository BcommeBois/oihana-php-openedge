<?php

namespace oihana\openedge\db\helpers ;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\enums\OpenEdge;

/**
 * Returns the row url and prefix it with the baseUrl of the application.
 *
 * @param Container         $container  The DI container reference.
 * @param string            $path       The pathname of the ressource.
 * @param string|array|null $expression The expression to encapsulate.
 * @param string            $definition The name of the definition in the container to give the base path of the url.
 *
 * @return array
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @example
 * The definition :
 * ```
 * OpenEdge::COLUMNS =
 * [
 *    'url' => rowUrl( $container , 'products' , [ SQL::COLUMN => 'products.id' , SQL::CAST => [ Type::VARCHAR , 255 ] ] )
 * ]
 * ```
 * is same as
 * ```
 * OpenEdge::COLUMNS =
 * [
 *    'url' =>
 *    [
 *       SQL::CONCAT =>
 *       [
 *          'https://api-staging.bcommebois.fr/products/'  ,
 *          [ SQL::COLUMN => 'products.cod_pro' , SQL::CAST => [ Type::VARCHAR , 255 ] ]
 *       ]
 *    ]
 * ]
 * ```
 */
function rowUrl
(
    Container         $container ,
    string            $path       = Char::EMPTY ,
    null|string|array $expression = null ,
    string            $definition = 'baseUrl'
)
:array
{
    $basePath = Char::EMPTY ;

    if( $container->has( $definition ) )
    {
        $basePath = $container->get( $definition ) ?? Char::EMPTY ;
    }

    return
    [
        OpenEdge::CONCAT =>
        [
            $basePath . $path . Char::SLASH  ,
            $expression // TODO use the expression function() here
        ]
    ] ;
}