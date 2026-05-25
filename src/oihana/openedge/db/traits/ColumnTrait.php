<?php

namespace oihana\openedge\db\traits;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\Char;
use oihana\openedge\enums\OpenEdge;

use oihana\reflect\exceptions\ConstantException;
use function oihana\core\arrays\isAssociative;
use function oihana\core\strings\compile;
use function oihana\openedge\db\helpers\asAlias;
use function oihana\openedge\db\helpers\expression;

trait ColumnTrait
{
    /**
     * The 'columns' definition to build the SELECT clause.
     * @var string|array|null
     */
    public string|array|null $columns = null ;

    // [OK] concat -> specials.a_tab || ',' || specials.inti_tab || ',' || ...
    // TODO [ OpenEdge::SELECT => 'SELECT ...' ]
    // [OK] [ OpenEdge::COLUMN => 'products.nom_pro' , OpenEdge::ALTER => StringFunction::LOWER ]
    // TODO [ OpenEdge::COLUMN => 'xxx' , OpenEdge::ALTER => AggregateFunction::AVG ]
    // [OK] [ OpenEdge::COLUMN => [  [ OpenEdge::COLUMN => ... ] , [ OpenEdge::COLUMN => ... ] ] ]
    // [OK] use column logic in the WHERE conditions and ON conditions
    // TODO ? OpenEdge::COLUMNS  => [ 'products.lib_conv' ] ?? direct insert the column name in the array ?? mix numeric index and associative index

    /**
     * Generates the columns list expression of the SELECT query.
     * @param array|string|null $init
     * @param string $separator
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     * @throws DateInvalidTimeZoneException
     * @throws DateMalformedStringException
     * @throws ConstantException
     *
     * @example
     * 1 - Use the columns property :
     * ``` php
     * $openedge->columns =
     * [
     *      'cod_cli'         => 'id' ,
     *      'name'            => [ 'column' => 'nom' , 'table' => 'clients' , 'alter' => 'lower' ] ,
     *      'created'         => 'date_creation' ,
     *      'addressLocality' => 'ville' ,
     *      'addressCountry'  => 'pays'  ,
     *  ] ;
     *
     * echo $openedge->columnList() ; // clients.code_cli AS "id", LOWER(clients.nom_cli) AS "name', ...
     *  ```
     *
     * 2 - Use the init parameter of the method :
     * ``` php
     * $init =
     * [
     *    'id'          => [ OpenEdge::COLUMN => 'products.index' , OpenEdge::CAST => Type::INTEGER ] ,
     *    'name'        => [ OpenEdge::COLUMN => 'nom_produit' , OpenEdge::TABLE => 'products' , OpenEdge::ALT => StringFunction::LOWER ] ,
     *    'created'     => [ OpenEdge::COLUMN => 'dateCreated' , OpenEdge::TABLE => 'products' ] ,
     *    'status'      => [ OpenEdge::COLUMN => 'CAST(products.statut AS INTEGER)' ] ,
     *    'billingUnit' => [ OpenEdge::COLUMN => 'billingUnit' , OpenEdge::ALT => StringFunction::LOWER , OpenEdge::TABLE => 'products' ] ,
     *    'height'      => [ OpenEdge::COLUMN => 'products.hauteur[1]' , OpenEdge::CAST => [ Type::FLOAT , 8 ]   ] ,
     * ] ;
     *
     * echo $openedge->columnList( $init ) ;
     * // SELECT CAST(products.index AS INTEGER) AS "id" ,
     *           LOWER(clients.nom_cli) AS "name" ,
     *           products.dateCreated AS "created" ,
     *           CAST(products.statut AS INTEGER) as "status" ,
     *           LOWER(products.billingUnit) as "billingUnit" ,
     *           CAST(products.hauteur[1] AS FLOAT(8)) as "height" ,
     *           ...
     * ```
     */
    public function columnList
    (
        array|string|null $init      = [] ,
        string            $separator = Char::COMMA . Char::SPACE
    )
    :string
    {
        if( is_string( $init ) )
        {
            return $init ;
        }

        $definitions = $init[ OpenEdge::COLUMNS ] ?? $this->columns ;

        if( is_string( $definitions ) )
        {
            return $definitions ;
        }
        else if( is_array( $definitions ) && isAssociative( $definitions ) )
        {
            $columns = [] ;
            foreach( $definitions as $key => $definition )
            {
                if( !empty( $key ) )
                {
                    if ( is_string( $definition ) && $definition !== Char::EMPTY && $key !== $definition )
                    {
                        $columns[] = asAlias( $definition , $key ) ;
                    }
                    else if( is_array( $definition ) && isAssociative( $definition ) )
                    {
                        $column    = expression( $definition , $key ) ;
                        $columns[] = asAlias( $column , $column == $key ? null : $key ) ;
                    }
                    else
                    {
                        $columns[] = $key ;
                    }
                }
            }

            if( count( $columns ) > 0 )
            {
                return compile( $columns , $separator ) ;
            }
        }

        return OpenEdge::ALL  ;
    }
}