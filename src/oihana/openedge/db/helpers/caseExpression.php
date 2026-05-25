<?php

namespace oihana\openedge\db\helpers;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\db\enums\Conditions;
use oihana\openedge\enums\OpenEdge;
use oihana\reflect\exceptions\ConstantException;

use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\cases\elseExpression;
use function oihana\openedge\db\helpers\cases\whenThenExpression;

/**
 * Specifies a series of search conditions and associated result expressions.
 *
 * @param array $definition
 * Array defining the case, possible keys:
 * - `OpenEdge::CASE` (array) : The name of the bind variable.
 * - Other keys supported by `overrideExpression()` (e.g., CAST, ALTER, ALTERS)
 *
 * @param callable|null $map
 * Optional callback to transform the arguments before passing them to the function.
 *
 * @return string
 *
 * @throws ConstantException
 * @throws DateInvalidTimeZoneException
 * @throws DateMalformedStringException
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @see https://docs.progress.com/bundle/openedge-sql-reference/page/CASE.html
 */
function caseExpression( array $definition = [] , ?callable $map = null ) :string
{
    $case = $definition[ OpenEdge::CASE ] ?? null ;

    if ( !is_array( $case ) && !isAssociative( $case ) )
    {
        return Char::EMPTY ;
    }

    $simple = false;
    $expressions = [ Conditions::CASE ] ;

    if ( isset( $case[ OpenEdge::EXPRESSION ] ) ) // simple case expression
    {
        $exprFunc      = $map ?? fn(...$args) => expression( ...$args ) ;
        $expressions[] = $exprFunc( $case[ OpenEdge::EXPRESSION ] ) ;
        $simple        = true;
    }

    $expressions[] = whenThenExpression( $case , $simple , $map ) ;
    $expressions[] = elseExpression( $case , $map ) ;
    $expressions[] = Conditions::END ;

    return overrideExpression
    (
        expression : compile( $expressions ) ,
        definition : $definition ,
        map        : $map
    );
}

/*

'essence' =>  // multiple search conditions -> CASE WHEN search_conditions THEN .. ELSE .. END
[
    SQL::CASE =>
    [
        SQL::CONDITIONS =>
        [
            [
                SQL::WHEN => [ [ [ SQL::COLUMN => 'specials.a_tab' ] , Predicate::IN , 0 , 1 , 4, 5 , 6 , 7 ] ] ,
                SQL::THEN => [ SQL::COLUMN => 'comp3.zc0' ]
            ]
        ],
    ]
] ,

SELECT ... ,
       CASE
           WHEN specials.a_tab IN (0,1,4,5,6,7) THEN comp3.zc0
           ELSE NULL
       END AS "essence"
FROM ...

SELECT
    ... ,

    CASE
        WHEN specials.a_tab IN (0,1,4,5,6,7) THEN INITCAP(comp3.zc0)
        ELSE NULL
    END AS "essence" ,

    CASE
        WHEN specials.a_tab IN (2,8,9) THEN INITCAP(comp3.zc0)
        WHEN specials.a_tab IN (6,7) THEN INITCAP(comp4.zc0)
        WHEN specials.a_tab = 4 THEN INITCAP(comp6.zc0)
        ELSE NULL
    END AS "type" ,

    ...

FROM ...

'essence' => // simple search conditions -> CASE expression WHEN..THEN .. ELSE .. END
[
   SQL::CASE =>
   [
       SQL::EXPRESSION => [ SQL::COLUMN => 'specials.a_tab' ] // OPTIONAL
       SQL::CONDITIONS =>
       [
           [
               SQL::WHEN => 0 ,
               SQL::THEN => [ SQL::COLUMN => 'comp3.zc0' ]
           ],
           [
               SQL::WHEN => 1 ,
               SQL::THEN => [ SQL::COLUMN => 'comp3.zc0' ]
           ],
           [
               SQL::WHEN => 2 ,
               SQL::THEN => [ SQL::COLUMN => 'comp3.zc0' ]
           ]
       ],
       SQL::ELSE => null // OPTIONAL
   ],
]
*/